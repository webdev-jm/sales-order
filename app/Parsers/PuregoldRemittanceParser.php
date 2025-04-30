<?php
namespace App\Parsers;

use App\Contracts\RemittanceParserInterface;
use App\Models\UploadTemplate;
use App\Models\AccountUploadTemplate;

class PuregoldRemittanceParser implements RemittanceParserInterface
{
    protected $uploadTemplate;
    protected $accountTemplate;
    protected $templateFieldsMap;

    public function __construct(UploadTemplate $uploadTemplate, AccountUploadTemplate $accountTemplate, $templateFieldsMap)
    {
        $this->uploadTemplate = $uploadTemplate;
        $this->accountTemplate = $accountTemplate;
        $this->templateFieldsMap = $templateFieldsMap;
    }

    public function parse(array $data): array
    {
        $remittanceData = [];

        $headers = $data['headers'] ?? [];
        $details = $data['details'] ?? [];

        // Parse headers
        foreach ($headers as $key => $header) {
            $assoc = [];

            foreach (array_slice($header, 0, 4) as $row) {
                for ($i = 0; $i < count($row); $i += 2) {
                    $indexVal = trim(str_replace(':', '', $row[$i]));
                    $value = $row[$i + 1] ?? null;
                    $assoc[$indexVal] = trim($value);
                }
            }

            if (array_key_exists('CREDIT NOTES', $assoc)) {
                $remittanceData[$key]['header'] = $assoc;
            }
        }

        // Custom field mapping for part 2
        $customTemplate = [
            1 => 'transaction_type',
            8 => 'store',
            12 => 'apv_no',
            15 => 'description',
            22 => 'rc_no',
            26 => 'rc_date',
            27 => 'po_no',
            28 => 'invoice_number',
            33 => 'invoice_amount',
            32 => 'due_date',
            34 => 'rc_amount',
            36 => 'ewt',
            39 => 'net_amount',
            42 => 'vat',
        ];

        // Parse detail lines
        foreach ($details as $key => $detailRows) {
            $detailData = [];
            $part = 1;

            

            foreach ($detailRows as $index => $detail) {

                if($detail[1] == 'TOTAL CREDIT AMOUNT') {
                    break;
                }

                if ($detail[1] === 'DEBIT NOTES') {
                    $part = 2;
                    continue;
                }

                if (empty($detail[1])) continue;

                if ($part === 1) {
                    foreach ($this->uploadTemplate->template_fields as $field) {
                        $fieldMap = $this->templateFieldsMap[$field->id] ?? null;
                        if (!$fieldMap) continue;

                        $columnKey = $this->accountTemplate->type === 'number'
                            ? $fieldMap['column_number']
                            : $fieldMap['column_name'];

                        $value = $detail[$columnKey] ?? null;
                        $detailData[$index][$field->column_name] = $value;
                    }
                } else {
                    foreach ($customTemplate as $fieldIndex => $fieldName) {
                        $value = $detail[$fieldIndex] ?? null;
                        $detailData[$index][$fieldName] = $value;
                    }
                }

                
            }

            $remittanceData[$key]['details'] = $detailData;
        }

        return $remittanceData;
    }
}