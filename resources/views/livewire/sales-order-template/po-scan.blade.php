<div>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-robot mr-1"></i> Scan Account PO (PDF)</h3>
            <div class="card-tools">
                <span class="badge badge-light p-2">
                    AI tokens this month: {{ number_format($tokensUsed) }}
                    @if($tokenLimit)
                        / {{ number_format($tokenLimit) }}
                    @endif
                </span>
                @if($dailyLimit)
                    <span class="badge badge-light p-2">
                        Your tokens today: {{ number_format($tokensToday) }} / {{ number_format($dailyLimit) }}
                    </span>
                @endif
            </div>
        </div>

        @if($aiProblem)
            <div class="card-body">
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ $aiProblem }}
                </div>
            </div>
        @else
            <div class="card-body">
                <p class="text-muted mb-2">
                    Choose the account the PO came from, then the PDF. The AI reads the PO, including
                    scanned paper, and fills the same review table as a CSV upload.
                    Always check the lines against the PDF before exporting.
                </p>
                <p class="small mb-3">
                    @foreach($aiProviders as $index => $provider)
                        @if($index === 1)
                            <span class="text-muted mx-1">then, if it fails,</span>
                        @elseif($index > 1)
                            <span class="text-muted mx-1">then</span>
                        @endif
                        <span class="badge {{ $provider->setupProblem() === null ? 'badge-info' : 'badge-light text-muted' }}"
                              title="{{ $provider->setupProblem() ?? $provider->model() }}">
                            <i class="fas fa-robot mr-1"></i>{{ $provider->label() }} · {{ $provider->model() }}
                            @if($provider->setupProblem() !== null)
                                (no key, skipped)
                            @endif
                        </span>
                    @endforeach
                </p>

                @if($tokenLimit && $tokensUsed >= $tokenLimit)
                    <div class="alert alert-danger">
                        <i class="fas fa-ban mr-1"></i>
                        This month's AI token limit has been reached. Ask an administrator to raise it on the Settings page.
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="scan_account_id">Account <span class="text-danger">*</span></label>
                            <div class="input-group mb-1">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" wire:model.live.debounce.300ms="accountSearch"
                                       class="form-control" placeholder="Filter accounts…">
                            </div>
                            <select wire:model.live="account_id" id="scan_account_id" size="6"
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

                    <div class="col-lg-4 col-md-6">
                        <div class="form-group">
                            <label for="scan_format_id">PO Format</label>
                            @if($account_id === '')
                                <p class="form-control-plaintext text-muted">Select an account first.</p>
                            @elseif($formats->isEmpty())
                                <p class="form-control-plaintext text-muted">
                                    This account has no PO format yet, so general reading rules are used.
                                    @can('po scan format manage')
                                        <a href="#" wire:click.prevent="$dispatch('openTab', ['formats'])">Add a format</a>
                                    @endcan
                                </p>
                            @else
                                <select wire:model.live="format_id" id="scan_format_id"
                                        class="form-control @error('format_id') is-invalid @enderror">
                                    <option value="">General rules (no format)</option>
                                    @foreach($formats as $format)
                                        <option value="{{ $format->id }}">{{ $format->name }}</option>
                                    @endforeach
                                </select>
                                @error('format_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="scan_files">PO PDF <span class="text-danger">*</span></label>
                            <input type="file" wire:model.live="files" id="scan_files" accept=".pdf,application/pdf" multiple
                                   class="form-control @error('files') is-invalid @enderror @error('files.*') is-invalid @enderror">
                            <small class="form-text text-muted">
                                Up to {{ $maxFiles }} PDFs, {{ $maxFileMb }} MB each.
                            </small>
                            @error('files')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            @error('files.*')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <div wire:loading wire:target="files" class="text-muted small mt-1">
                                <i class="fas fa-spinner fa-spin"></i> Uploading…
                            </div>
                        </div>

                        @if($cacheDays > 0)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" wire:model="readAgain" id="scan_read_again" class="custom-control-input">
                                <label for="scan_read_again" class="custom-control-label font-weight-normal">
                                    Read again with AI, even if this PDF was scanned before
                                </label>
                                <small class="form-text text-muted">
                                    A PDF scanned in the last {{ $cacheDays }} days is reused at no token cost unless this is ticked.
                                </small>
                            </div>
                        @endif
                    </div>
                </div>

                @if(!empty($scanErrors))
                    <div class="alert alert-danger mb-0">
                        <p class="mb-1"><strong>These PDFs could not be scanned:</strong></p>
                        <ul class="mb-0 pl-3">
                            @foreach($scanErrors as $fileName => $message)
                                <li><strong>{{ $fileName }}</strong> — {{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <div class="card-footer">
                <button wire:click="scan" wire:loading.attr="disabled" wire:target="scan,files"
                        type="button" class="btn btn-primary">
                    <span wire:loading.remove wire:target="scan">
                        <i class="fas fa-magic mr-1"></i> Scan PDF
                    </span>
                    <span wire:loading wire:target="scan">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Reading the PO… this can take a minute
                    </span>
                </button>
            </div>
        @endif
    </div>
</div>
