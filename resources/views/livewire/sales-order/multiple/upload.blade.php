<div>


    <form wire:submit.prevent="checkFileData">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">UPLOAD MULTIPLE SO<i class="fa fa-spinner-fa-spin" wire:loading></i></h3>
                <div class="card-tools">
                    <a href="{{asset('/assets/SMS Multiple PO upload Format.xlsx')}}" class="btn btn-success btn-sm">
                        <i class="fa fa-download mr-1"></i>
                        DOWNLOAD TEMPLATE
                    </a>
                </div>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label for="">UPLOAD FILE</label>
                            @can('po scan access')
                                <input type="file" class="form-control{{$errors->has('so_files') || $errors->has('so_files.*') ? ' is-invalid' : ''}}" wire:model="so_files" accept=".xls, .xlsx, .pdf" multiple>
                                <small class="text-muted">One Excel file, or up to {{config('ai.po_scan.max_files')}} PO PDFs.</small>
                            @else
                                <input type="file" class="form-control{{$errors->has('so_files') || $errors->has('so_files.*') ? ' is-invalid' : ''}}" wire:model="so_files" accept=".xls, .xlsx" multiple>
                            @endcan
                            @error('so_files')<p class="text-danger mb-0">{{$message}}</p>@enderror
                            @foreach($errors->get('so_files.*') as $messages)
                                <p class="text-danger mb-0">{{$messages[0]}}</p>
                            @endforeach
                        </div>
                    </div>
                    @can('po scan access')
                    <div class="col-lg-8">
                        <p class="text-muted small mt-lg-4">
                            <i class="fa fa-magic mr-1"></i>
                            PO PDFs are read with AI. Each purchase order found becomes one sales order, with its PDF attached as the PO document.
                        </p>
                    </div>
                    @endcan
                </div>

                @if(!empty($scanErrors))
                    <div class="alert alert-danger pl-0 mb-0">
                        <strong class="ml-3">These PDFs could not be fully read:</strong>
                        <ul class="mb-0">
                            @foreach($scanErrors as $file_name => $message)
                                <li>{{$file_name}} &mdash; {{$message}}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>
            <div class="card-footer text-right">
                <span class="text-muted mr-2" wire:loading wire:target="checkFileData">Reading the files, PDFs take a few seconds each&hellip;</span>
                <button class="btn btn-primary" type="submit" wire:loading.attr="disabled">
                    <i class="fa fa-upload mr-1" wire:loading.remove></i>
                    <i class="fa fa-spinner fa-spin mr-1" wire:loading></i>
                    UPLOAD
                </button>
            </div>
        </div>
    </form>

    @if(!empty($so_data))
    <div class="row">
        <div class="col-12 mb-3">
            <button class="btn btn-secondary" wire:click.prevent="saveAll('draft')" wire:loading.attr="disabled">
                <i class="fa fa-spinner fa-spin mr-1" wire:loading></i>
                DRAFT ALL
            </button>
            <button class="btn btn-primary" id="btn-summary-modal" wire:click="setSoData" wire:loading.attr="disabled">
                <i class="fa fa-list mr-1"></i>
                VIEW SUMMARY
            </button>
        </div>

        @foreach($so_data as $po_number => $data)
            <div class="col-12" wire:key="so-card-{{$data['key'] ?? $loop->index}}">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">CONTROL NUMBER: <b>{{$success_data[$po_number]['control_number'] ?? 'N/A'}}</b></div>
                        <div class="card-tools">
                            @if(empty($success_data[$po_number]['control_number']))
                                <button class="btn btn-secondary" wire:loading.attr="disabled" wire:click.prevent="saveSalesOrder('draft', '{{$po_number}}')">
                                    <i class="fa fa-spinner fa-spin mr-1" wire:loading></i>
                                    Save as Draft
                                </button>
                                <button class="btn btn-success" wire:loading.attr="disabled" wire:click.prevent="saveSalesOrder('finalized', '{{$po_number}}')">
                                    <i class="fa fa-spinner fa-spin mr-1" wire:loading></i>
                                    Finalize
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">

                        @if(!empty($err_data[$po_number]))
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-danger pl-0">
                                        <ul class="mb-0">
                                            @foreach($err_data[$po_number] as $err)
                                                <li class="">{{$err}}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($data['warnings']))
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-warning pl-0">
                                        <strong class="ml-3">The following SKUs were not included in this order:</strong>
                                        <ul class="mb-0">
                                            @foreach($data['warnings'] as $warning)
                                                <li>{{$warning['stock_code']}} &mdash; {{$warning['reason']}}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($success_data[$po_number]['message']))
                            <div class="row">
                                <div class="col-12">
                                    <div class="alert alert-success">
                                        {{$success_data[$po_number]['message']}}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-12">
                                <h4>
                                    {{$success_data[$po_number]['control_number'] ?? 'N/A'}}
                                    <small class="float-right">
                                        @if(!empty($success_data[$po_number]['status']))
                                            <span class="badge {{$success_data[$po_number]['status'] == 'draft' ? 'bg-secondary' : 'bg-success'}}">
                                                {{$success_data[$po_number]['status']}}
                                            </span>
                                        @else
                                            <span class="badge badge-info">
                                                preview
                                            </span>
                                        @endif
                                    </small>
                                </h4>
                            </div>
                        </div>

                        <div class="row invoice-info">
                            <div class="col-sm-4 invoice-col">
                                Ship to Address
                                <address>
                                    @if(!empty($data['shipping_address']))
                                        <strong>{{$data['shipping_address']['ship_to_name']}}</strong><br>
                                        {{$data['shipping_address']['building']}}<br>
                                        {{$data['shipping_address']['street']}}<br>
                                        {{$data['shipping_address']['city']}}<br>
                                        {{$data['shipping_address']['postal']}}
                                    @else
                                        <strong>{{$account->account_name}}</strong><br>
                                        {{$account->ship_to_address1}}<br>
                                        {{$account->ship_to_address2}}<br>
                                        {{$account->ship_to_address3}}<br>
                                        {{$account->postal_code}}
                                    @endif
                                </address>
                            </div>

                            <div class="col-sm-4 invoice-col">
                                <b>PO Number: {{$po_number}}</b><br>
                                @if(empty($success_data[$po_number]) && !empty($data['key']))
                                    <div class="form-inline my-1">
                                        <label class="font-weight-bold mr-1" for="po-value-{{$data['key']}}">PO Value:</label>
                                        <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="po-value-{{$data['key']}}" value="{{$data['po_value'] ?: ''}}" wire:change="updateOrderField('{{$data['key']}}', 'po_value', $event.target.value)">
                                    </div>
                                @else
                                    <b>PO Value:</b> {{number_format($data['po_value'], 2)}}<br>
                                @endif
                                <b>Order Date:</b> {{date('Y-m-d')}}<br>
                                @if(empty($success_data[$po_number]) && !empty($data['key']))
                                    <div class="form-inline my-1">
                                        <label class="font-weight-bold mr-1" for="ship-date-{{$data['key']}}">Ship Date:</label>
                                        <input type="date" class="form-control form-control-sm" id="ship-date-{{$data['key']}}" value="{{$data['ship_date']}}" wire:change="updateOrderField('{{$data['key']}}', 'ship_date', $event.target.value)">
                                    </div>
                                @else
                                    <b>Ship Date:</b> {{$data['ship_date']}}<br>
                                @endif
                                <b>Discount:</b> {{$account->discount->description ?? ''}}<br>
                                <b>Account:</b> [{{$account->account_code}}] {{$account->short_name}}
                            </div>

                            <div class="col-sm-4 invoice-col">
                                <b>Reference:</b> <br>
                                <b>Shipping Instruction:</b><br>
                                <p>
                                    {{$data['shipping_instruction']}}
                                </p>
                                @if(!empty($data['key']))
                                    <b>PO Attachment:</b>
                                    @if(!empty($attachments[$data['key']]))
                                        <div>
                                            <i class="fa {{strtolower($attachments[$data['key']]->getClientOriginalExtension()) === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file'}} mr-1"></i>{{$attachments[$data['key']]->getClientOriginalName()}}
                                            @if(($data['scanned_from'] ?? null) === $attachments[$data['key']]->getClientOriginalName())
                                                <span class="badge badge-info ml-1">scanned PO</span>
                                            @endif
                                            @if(empty($success_data[$po_number]))
                                                <a href="#" class="badge badge-danger ml-1" wire:click.prevent="removeAttachment('{{$data['key']}}')"><i class="fa fa-times mr-1"></i>Remove</a>
                                            @endif
                                        </div>
                                    @elseif(empty($success_data[$po_number]))
                                        <input type="file" class="form-control-file" wire:model.live="attachments.{{$data['key']}}" accept="{{\App\Services\SalesOrderAttachmentService::accept()}}">
                                        <small class="text-muted" wire:loading wire:target="attachments.{{$data['key']}}">Uploading&hellip;</small>
                                    @else
                                        <span class="text-muted">none</span>
                                    @endif
                                    @error('attachments.'.$data['key'])<p class="text-danger mb-0">{{$message}}</p>@enderror
                                @endif
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-12 table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-thead">
                                        <tr>
                                            <th>#</th>
                                            <th class="align-middle">Stock Code</th>
                                            <th class="align-middle">Description</th>
                                            <th class="align-middle">Unit</th>
                                            <th class="align-middle">Quantity</th>
                                            <th class="align-middle">Total</th>
                                            <th class="align-middle">Total less discount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $num = 0;
                                            $quantity_total = 0;
                                            $sales_total = 0;
                                            $sales_total_less_disc = 0;
                                        @endphp
                                        @foreach($data['lines'] as $key => $val)
                                            @php
                                                $num++;
                                            @endphp
                                            <tr>
                                                <td class="align-middle text-center">{{$num}}</td>
                                                <td class="align-middle">{{$val['sku_code']}}</td>
                                                @if(!empty($val['product']))
                                                    <td class="align-middle">{{$val['product']['description']}} [{{$val['product']['size']}}]</td>
                                                @else
                                                    <td class="align-middle">
                                                        product not found
                                                    </td>
                                                @endif
                                                <td>{{$val['uom']}}</td>
                                                <td class="text-right">{{$val['quantity']}}</td>
                                                <td class="text-right">{{number_format($val['total'], 2)}}</td>
                                                <td class="text-right">{{number_format($val['total_less_discount'], 2)}}</td>
                                            </tr>
                                            @php
                                                $quantity_total += $val['quantity'];
                                                $sales_total += $val['total'];
                                                $sales_total_less_disc += $val['total_less_discount'];
                                            @endphp
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="4">TOTAL</th>
                                            <th class="text-right">{{number_format($quantity_total)}}</th>
                                            <th class="text-right">{{number_format($sales_total, 2)}}</th>
                                            <th class="text-right">{{number_format($sales_total_less_disc, 2)}}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <p class="lead">Order Summary</p>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tr>
                                            <th style="width:50%">Total:</th>
                                            <td class="text-right">{{number_format($sales_total, 2)}}</td>
                                        </tr>
                                        <tr>
                                            <th>Discount</th>
                                            <td class="text-right">{{$account->discount->description ?? ''}}</td>
                                        </tr>
                                        <tr>
                                            @php
                                                $discounted_total = $sales_total;
                                                if(!empty($data['discount'])) {
                                                    $discounts = [$data['discount']['discount_1'], $data['discount']['discount_2'], $data['discount']['discount_3']];

                                                    foreach ($discounts as $discountValue) {
                                                        if ($discountValue > 0) {
                                                            $discounted_total = $discounted_total * ((100 - $discountValue) / 100);
                                                        }
                                                    }
                                                }
                                            @endphp
                                            <th>Total Less Discount</th>
                                            <td class="text-right">{{number_format($discounted_total, 2)}}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

</div>
