<div>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                {{ $editingId ? 'Edit PO Format' : 'Add PO Format' }}
            </h3>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Each account prints its POs differently. Describe this account's PO in plain words and the AI
                will follow it when scanning. Mention only what differs from a normal PO.
            </p>

            @if(session()->has('scan_format_saved'))
                <div class="alert alert-success py-2">
                    <i class="fas fa-check mr-1"></i> {{ session('scan_format_saved') }}
                </div>
            @endif

            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="form-group">
                        <label for="format_account_id">Account <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm mb-1">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" wire:model.debounce.300ms="accountSearch"
                                   class="form-control" placeholder="Filter accounts…">
                        </div>
                        <select wire:model="account_id" id="format_account_id" size="6"
                                class="form-control @error('account_id') is-invalid @enderror"
                                style="height:auto;">
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">
                                    [{{ $account->account_code }}] {{ $account->account_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('account_id')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-lg-8 col-md-6">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="format_name">Format Name <span class="text-danger">*</span></label>
                            <input type="text" wire:model.defer="name" id="format_name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g. Standard PO">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label for="format_uom">Default Unit</label>
                            <select wire:model.defer="default_uom" id="format_uom"
                                    class="form-control @error('default_uom') is-invalid @enderror">
                                @foreach($uoms as $uom)
                                    <option value="{{ $uom }}">{{ $uom }}</option>
                                @endforeach
                            </select>
                            @error('default_uom')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group col-md-3">
                            <label class="d-block">&nbsp;</label>
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" wire:model.defer="is_active" class="custom-control-input" id="format_active">
                                <label class="custom-control-label" for="format_active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label for="format_instructions">Notes for the AI</label>
                        <textarea wire:model.defer="instructions" id="format_instructions" rows="6"
                                  class="form-control @error('instructions') is-invalid @enderror"
                                  placeholder="e.g. The PO number is labelled &quot;Order No.&quot;. The item code is in the &quot;Article&quot; column; ignore the &quot;Vendor Item&quot; column. Quantities are in pieces. The store code is the 4-digit number before the store name under &quot;Deliver To&quot;. Dates are DD/MM/YYYY."></textarea>
                        @error('instructions')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            @if($editingId)
                <button wire:click="cancel" type="button" class="btn btn-default mr-1">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
            @endif
            <button wire:click="save" wire:loading.attr="disabled" wire:target="save" type="button" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Save Format
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PO Formats</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm" style="width:240px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Account or format…">
                </div>
            </div>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-sm table-hover mb-0" style="font-size:0.85rem;">
                <thead class="thead-light">
                    <tr>
                        <th>Account</th>
                        <th>Format</th>
                        <th>Unit</th>
                        <th>Notes for the AI</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($formats as $format)
                        <tr wire:key="scan-format-{{ $format->id }}" class="{{ $editingId === $format->id ? 'table-active' : '' }}">
                            <td class="text-nowrap">[{{ $format->account->account_code ?? '' }}] {{ $format->account->account_name ?? '—' }}</td>
                            <td>{{ $format->name }}</td>
                            <td>{{ $format->default_uom }}</td>
                            <td class="text-muted">{{ \Illuminate\Support\Str::limit((string) $format->instructions, 120) ?: '—' }}</td>
                            <td>
                                @if($format->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-nowrap">
                                <button wire:click="edit({{ $format->id }})" type="button" class="btn btn-warning btn-xs mr-1">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button wire:click="delete({{ $format->id }})"
                                        onclick="confirm('Delete this PO format?') || event.stopImmediatePropagation()"
                                        type="button" class="btn btn-danger btn-xs">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No PO formats yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span class="text-muted small">{{ $formats->total() }} format(s)</span>
            {{ $formats->links() }}
        </div>
    </div>
</div>
