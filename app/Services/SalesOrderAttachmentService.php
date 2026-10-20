<?php

namespace App\Services;

use App\Models\SalesOrder;
use App\Models\SalesOrderAttachment;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores the optional PO document of a sales order. An order keeps one attachment: attaching
 * a new file replaces the old one.
 *
 * A file can also be held in the session before its order exists, for the single-order form:
 * the PDF it was scanned from, or the attachment of the order being resubmitted. The held file
 * is attached when the order is saved.
 */
class SalesOrderAttachmentService
{
    public const PENDING_SESSION_KEY = 'so_pending_attachment';

    /**
     * Validation rules for an optional attachment upload.
     *
     * @return array<int, string>
     */
    public static function rules(): array
    {
        return [
            'nullable',
            'file',
            'mimes:' . implode(',', config('sales-order.attachments.mimes')),
            'max:' . config('sales-order.attachments.max_kb'),
        ];
    }

    /**
     * Value for a file input's accept attribute, e.g. ".pdf,.jpg".
     */
    public static function accept(): string
    {
        return collect(config('sales-order.attachments.mimes'))->map(fn ($ext) => '.' . $ext)->implode(',');
    }

    /**
     * @param array<string, mixed>|null $scan what the AI read and the user reviewed (see scanRecord())
     */
    public function attach(SalesOrder $salesOrder, UploadedFile $file, string $source = SalesOrderAttachment::SOURCE_UPLOAD, ?array $scan = null): SalesOrderAttachment
    {
        $path = $this->disk()->putFileAs(
            $this->directory() . '/' . $salesOrder->id,
            $file,
            $this->storedName($file->getClientOriginalName())
        );

        return $this->record($salesOrder, [
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'source'    => $source,
        ] + $this->scanColumns($scan));
    }

    /**
     * The scan of a reviewed PO as it is kept with the order: what the AI read, the confidence of each value,
     * and what the user changed or confirmed.
     *
     * @param array<string, mixed> $order one entry of SalesOrderPdfReader::read()
     * @param array<int, bool> $included
     * @param array<int, mixed> $quantities
     * @param array<string, bool> $confirmed
     * @return array<string, mixed>
     */
    public static function scanRecord(array $order, array $confidence, string $fileName, array $included = [], array $quantities = [], array $confirmed = []): array
    {
        return [
            'file'       => $fileName,
            'po_number'  => $order['po_number'],
            'reviewed_at'=> now()->toDateTimeString(),
            'score'      => $confidence['score'],
            'level'      => $confidence['level'],
            'fields'     => collect($confidence['fields'])->map(fn (array $field, string $key) => [
                'label'     => $field['label'],
                'value'     => $field['value'],
                'score'     => $field['score'],
                'reasons'   => $field['reasons'],
                'confirmed' => (bool) ($confirmed['field-' . $key] ?? false),
            ])->all(),
            'lines'      => collect($order['lines'])->map(fn (array $line, int $index) => [
                'sku'        => $line['sku'],
                'description'=> $line['description'],
                'read'       => $line['read_quantity'] ?? $line['quantity'],
                'quantity'   => is_numeric($quantities[$index] ?? null) ? (float) $quantities[$index] : $line['quantity'],
                'uom'        => $line['so_uom'],
                'amount'     => $line['amount'],
                'stock_code' => $line['stock_code'],
                'included'   => ($included[$index] ?? true) !== false,
                'score'      => $confidence['lines'][$index]['score'] ?? null,
                'reasons'    => $confidence['lines'][$index]['reasons'] ?? [],
                'confirmed'  => (bool) ($confirmed['line-' . $index] ?? false),
            ])->all(),
        ];
    }

    public function remove(SalesOrder $salesOrder): void
    {
        SalesOrderAttachment::where('sales_order_id', $salesOrder->id)->get()->each(function (SalesOrderAttachment $attachment) {
            $this->disk()->delete($attachment->file_path);
            $attachment->delete();
        });
    }

    /**
     * Keeps an uploaded file until the order it belongs to is saved.
     */
    public function holdPending(UploadedFile $file, string $source = SalesOrderAttachment::SOURCE_SCAN, ?array $scan = null): void
    {
        $this->discardPending();

        $path = $this->disk()->putFileAs(
            $this->directory() . '/pending',
            $file,
            $this->storedName($file->getClientOriginalName())
        );

        Session::put(self::PENDING_SESSION_KEY, [
            'path'      => $path,
            'name'      => $file->getClientOriginalName(),
            'mime'      => $file->getMimeType(),
            'size'      => $file->getSize(),
            'source'    => $source,
            'temporary' => true,
            'scan'      => $scan,
        ]);
    }

    /**
     * Records the reviewed scan on the held PDF, so it is kept when the order is saved.
     *
     * @param array<string, mixed> $scan
     */
    public function setPendingScan(array $scan): void
    {
        $pending = Session::get(self::PENDING_SESSION_KEY);

        if (\is_array($pending)) {
            Session::put(self::PENDING_SESSION_KEY, ['scan' => $scan] + $pending);
        }
    }

    /**
     * Carries an existing order's attachment over to the order that replaces it. The file is
     * copied on save, so the original order keeps its own.
     */
    public function holdExisting(SalesOrderAttachment $attachment): void
    {
        $this->discardPending();

        Session::put(self::PENDING_SESSION_KEY, [
            'path'      => $attachment->file_path,
            'name'      => $attachment->file_name,
            'mime'      => $attachment->mime_type,
            'size'      => $attachment->file_size,
            'source'    => $attachment->source,
            'temporary' => false,
            'scan'      => $attachment->scan_result,
        ]);
    }

    /**
     * @return array{path: string, name: string, mime: ?string, size: ?int, source: string, temporary: bool}|null
     */
    public function pending(): ?array
    {
        $pending = Session::get(self::PENDING_SESSION_KEY);

        return \is_array($pending) && $this->disk()->exists($pending['path']) ? $pending : null;
    }

    public function discardPending(): void
    {
        $pending = Session::pull(self::PENDING_SESSION_KEY);

        if (\is_array($pending) && !empty($pending['temporary'])) {
            $this->disk()->delete($pending['path']);
        }
    }

    public function attachPending(SalesOrder $salesOrder): ?SalesOrderAttachment
    {
        $pending = $this->pending();

        if ($pending === null) {
            Session::forget(self::PENDING_SESSION_KEY);

            return null;
        }

        $path = $this->directory() . '/' . $salesOrder->id . '/' . $this->storedName($pending['name']);

        if ($pending['temporary']) {
            $this->disk()->move($pending['path'], $path);
        } else {
            $this->disk()->copy($pending['path'], $path);
        }

        Session::forget(self::PENDING_SESSION_KEY);

        return $this->record($salesOrder, [
            'file_path' => $path,
            'file_name' => $pending['name'],
            'mime_type' => $pending['mime'],
            'file_size' => $pending['size'],
            'source'    => $pending['source'],
        ] + $this->scanColumns($pending['scan'] ?? null));
    }

    public function disk(): FilesystemAdapter
    {
        return Storage::disk(config('sales-order.attachments.disk'));
    }

    /**
     * @param array<string, mixed> $attributes
     */
    /**
     * @param array<string, mixed>|null $scan
     * @return array{scan_result?: array<string, mixed>, scan_confidence?: int}
     */
    private function scanColumns(?array $scan): array
    {
        return $scan === null ? [] : ['scan_result' => $scan, 'scan_confidence' => (int) ($scan['score'] ?? 0)];
    }

    private function record(SalesOrder $salesOrder, array $attributes): SalesOrderAttachment
    {
        $previous = SalesOrderAttachment::where('sales_order_id', $salesOrder->id)->get();

        $attachment = SalesOrderAttachment::create($attributes + [
            'sales_order_id' => $salesOrder->id,
            'user_id'        => auth()->id(),
        ]);

        $previous->each(function (SalesOrderAttachment $old) {
            $this->disk()->delete($old->file_path);
            $old->delete();
        });

        $salesOrder->unsetRelation('attachment');

        return $attachment;
    }

    private function directory(): string
    {
        return config('sales-order.attachments.directory');
    }

    /**
     * A random name that keeps the original extension, so uploads never collide or overwrite.
     */
    private function storedName(string $originalName): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        return Str::uuid() . ($extension !== '' ? '.' . $extension : '');
    }
}
