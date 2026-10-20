<div class="scan-review">
    <div class="row">
        {{-- The PDF --}}
        <div class="col-xl-5 mb-3">
            <div class="card card-outline card-secondary h-100 mb-0">
                <div class="card-header d-flex align-items-center">
                    <h3 class="card-title mr-auto text-truncate"><i class="fa fa-file-pdf text-danger mr-1"></i>{{ $fileName }}</h3>
                    <a href="{{ route('sales-order.scan-review.pdf') }}" target="_blank" rel="noopener" class="btn btn-xs btn-outline-secondary">Open PDF</a>
                </div>
                <div class="card-body p-2" wire:ignore>
                    <div class="po-preview"
                        x-data="poPdfPreview(@js(route('sales-order.scan-review.pdf')), @js(asset('vendor/pdfjs/pdf.min.js')), @js(asset('vendor/pdfjs/pdf.worker.min.js')))"
                        x-on:pdf-find.window="find($event.detail)">
                        <p class="text-muted small mb-2" x-show="status" x-text="status"></p>
                        <p class="text-muted small mb-2" x-show="message" x-text="message"></p>
                        <div class="po-pages" x-ref="pages"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- What the AI read --}}
        <div class="col-xl-7 mb-3">
            <div class="card card-outline card-primary mb-0">
                <div class="card-header">
                    <div class="d-flex flex-wrap align-items-center" style="gap:.5rem">
                        <h3 class="card-title mr-auto">What the AI read</h3>
                        <span class="confidence-chip confidence-{{ $confidence['level'] }}">Overall {{ $levels[$confidence['level']] }} {{ $confidence['score'] }}</span>
                    </div>
                    @if(count($orders) > 1)
                        <div class="btn-group btn-group-sm flex-wrap mt-2" role="group" aria-label="Purchase orders in this PDF">
                            @foreach($orders as $poNumber => $item)
                                <button type="button" wire:key="po-{{ $poNumber }}" wire:click="selectPo({{ $poNumber }})"
                                    class="btn {{ $po === $poNumber ? 'btn-primary' : 'btn-outline-primary' }}">
                                    PO {{ $item['po_number'] !== '' ? $item['po_number'] : '(no number)' }} <span class="confidence-dot confidence-{{ $poLevels[$poNumber] }}" title="{{ $levels[$poLevels[$poNumber]] }}"></span>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="card-body">
                    @if($unchecked > 0)
                        <div class="alert alert-warning py-2">
                            <i class="fa fa-exclamation-triangle mr-1"></i>
                            {{ $unchecked }} {{ $unchecked === 1 ? 'value is' : 'values are' }} marked Low. Click each one to find it on the PDF, correct it if needed, then tick <b>I checked this</b>.
                        </div>
                    @else
                        <div class="alert alert-light border py-2 mb-3">
                            <i class="fa fa-check-circle text-success mr-1"></i>
                            Nothing needs a check before the order is created. Values marked Medium are worth a quick look.
                        </div>
                    @endif

                    <div class="scan-fields mb-3">
                        @foreach($confidence['fields'] as $key => $field)
                            <div class="scan-field confidence-border-{{ $field['level'] }}" wire:key="field-{{ $po }}-{{ $key }}"
                                role="button" tabindex="0"
                                x-on:click="$dispatch('pdf-find', { terms: @js($field['find']), page: @js($field['page']), row: false })"
                                x-on:keydown.enter="$dispatch('pdf-find', { terms: @js($field['find']), page: @js($field['page']), row: false })">
                                <div class="d-flex align-items-start justify-content-between" style="gap:.4rem">
                                    <span class="scan-label">{{ $field['label'] }}</span>
                                    <span class="confidence-chip confidence-{{ $field['level'] }}">{{ $levels[$field['level']] }} {{ $field['score'] }}</span>
                                </div>
                                @if($key === 'ship_date')
                                    <input type="date" class="form-control form-control-sm mt-1" id="ship-date-{{ $po }}" aria-label="Ship date"
                                        wire:model.live="shipDates.{{ $po }}" x-on:click.stop>
                                @else
                                    <div class="scan-value">{{ $field['value'] !== '' ? $field['value'] : '-' }}</div>
                                @endif
                                @foreach($field['reasons'] as $reason)
                                    <small class="d-block text-muted" wire:key="field-reason-{{ $key }}-{{ $loop->index }}">{{ $reason }}</small>
                                @endforeach
                                @if($field['level'] === 'low')
                                    <div class="custom-control custom-checkbox mt-1" x-on:click.stop>
                                        <input type="checkbox" class="custom-control-input" id="confirm-{{ $po }}-field-{{ $key }}" wire:model.live="confirmed.{{ $po }}.field-{{ $key }}">
                                        <label class="custom-control-label small" for="confirm-{{ $po }}-field-{{ $key }}">I checked this</label>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover scan-lines mb-0">
                            <thead>
                                <tr>
                                    <th title="Keep the line on the order">Keep</th>
                                    <th>Line read</th>
                                    <th class="text-right" style="width:6.5rem">Quantity</th>
                                    <th>Unit</th>
                                    <th class="text-right">Amount</th>
                                    <th>Product</th>
                                    <th style="min-width:13rem">Confidence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order['lines'] as $index => $line)
                                    @php
                                        $kept = $included[$po][$index] ?? true;
                                        $rated = $confidence['lines'][$index] ?? null;
                                    @endphp
                                    <tr wire:key="line-{{ $po }}-{{ $index }}" class="{{ $kept ? '' : 'scan-line-dropped' }} {{ $rated && $rated['level'] === 'low' && empty($confirmed[$po]['line-' . $index]) ? 'scan-line-open' : '' }}"
                                        x-on:click="$dispatch('pdf-find', { terms: @js(array_values(array_filter([$line['sku'], $line['description']]))), page: @js($line['page']), row: true })">
                                        <td x-on:click.stop>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="keep-{{ $po }}-{{ $index }}" wire:model.live="included.{{ $po }}.{{ $index }}">
                                                <label class="custom-control-label" for="keep-{{ $po }}-{{ $index }}"><span class="sr-only">Keep line {{ $index + 1 }}</span></label>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="scan-code">{{ $line['sku'] }}</span>
                                            <span class="d-block small">{{ $line['description'] }}</span>
                                        </td>
                                        <td class="text-right" x-on:click.stop>
                                            <input type="number" min="0" step="any" class="form-control form-control-sm text-right" id="qty-{{ $po }}-{{ $index }}"
                                                aria-label="Quantity for {{ $line['sku'] }}" wire:model.live.debounce.400ms="quantities.{{ $po }}.{{ $index }}" @disabled(!$kept)>
                                        </td>
                                        <td>{{ $line['so_uom'] }}@if($line['uom'] === '')<span class="text-muted small d-block">not printed</span>@endif</td>
                                        <td class="text-right text-nowrap">{{ is_numeric($line['amount']) ? number_format((float) $line['amount'], 2) : '-' }}</td>
                                        <td>
                                            @if($line['product_id'])
                                                <span class="scan-code">{{ $line['stock_code'] }}</span>
                                                <span class="d-block small text-muted">{{ $line['product'] }}</span>
                                            @else
                                                <span class="text-danger small">Not found</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($rated)
                                                <span class="confidence-chip confidence-{{ $rated['level'] }}">{{ $levels[$rated['level']] }} {{ $rated['score'] }}</span>
                                                @foreach($rated['reasons'] as $reason)
                                                    <small class="d-block text-muted" wire:key="line-reason-{{ $index }}-{{ $loop->index }}">{{ $reason }}</small>
                                                @endforeach
                                                @if($rated['level'] === 'low')
                                                    <div class="custom-control custom-checkbox mt-1" x-on:click.stop>
                                                        <input type="checkbox" class="custom-control-input" id="confirm-{{ $po }}-line-{{ $index }}" wire:model.live="confirmed.{{ $po }}.line-{{ $index }}">
                                                        <label class="custom-control-label small" for="confirm-{{ $po }}-line-{{ $index }}">I checked this</label>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-muted small">Left out</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted small mt-2 mb-0">Click a value or a line to mark it on the PDF. Lines without a matched product are left out of the order, as before; untick <b>Keep</b> to leave out any other line.</p>
                </div>

                <div class="card-footer d-flex flex-wrap align-items-center" style="gap:.5rem">
                    <button type="button" class="btn btn-outline-danger" wire:click="discard" wire:loading.attr="disabled">Discard</button>
                    <span class="mr-auto"></span>
                    @error('review')<span class="text-danger small">{{ $message }}</span>@enderror
                    <span class="small {{ $unchecked > 0 ? 'text-danger' : 'text-success' }}" wire:loading.remove wire:target="createSalesOrder">
                        {{ $unchecked > 0 ? $unchecked . ' to check first' : 'Ready. Opens the order form filled in.' }}
                    </span>
                    <button type="button" class="btn btn-primary" wire:click="createSalesOrder" wire:loading.attr="disabled" @disabled($unchecked > 0)>
                        <span wire:loading.remove wire:target="createSalesOrder">Create sales order</span>
                        <span wire:loading wire:target="createSalesOrder"><i class="fa fa-spinner fa-spin mr-1"></i>Opening the form&hellip;</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
