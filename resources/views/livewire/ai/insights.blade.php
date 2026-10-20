<div>
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-lightbulb mr-1"></i> AI insights</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-sm btn-info" wire:click="explain" wire:loading.attr="disabled" wire:target="explain">
                    <span wire:loading.remove wire:target="explain"><i class="fas fa-magic mr-1"></i> Explain {{ $currentPeriod }}</span>
                    <span wire:loading wire:target="explain"><i class="fas fa-spinner fa-spin mr-1"></i> Reading the figures…</span>
                </button>
                @if($insight)
                    <button type="button" class="btn btn-tool" wire:click="dismiss" title="Close"><i class="fas fa-times"></i></button>
                @endif
            </div>
        </div>

        @if($error !== '' || $insight)
            <div class="card-body">
                @if($error !== '')
                    <p class="text-warning mb-0"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $error }}</p>
                @endif

                @if($insight)
                    @if($insightPeriod !== $currentPeriod)
                        <p class="text-muted small mb-2"><i class="fas fa-info-circle mr-1"></i>This is about {{ $insightPeriod }}. Click Explain for {{ $currentPeriod }}.</p>
                    @endif
                    <p class="lead mb-2">{{ $insight['headline'] }}</p>
                    @if(!empty($insight['highlights']))
                        <ul class="mb-2">
                            @foreach($insight['highlights'] as $index => $point)
                                <li wire:key="highlight-{{ $index }}">{{ $point }}</li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($insight['watch']))
                        <p class="font-weight-bold mb-1 text-warning"><i class="fas fa-eye mr-1"></i>Worth checking</p>
                        <ul class="mb-2">
                            @foreach($insight['watch'] as $index => $point)
                                <li wire:key="watch-{{ $index }}">{{ $point }}</li>
                            @endforeach
                        </ul>
                    @endif
                    <small class="text-muted">Written by AI from the figures on this page. Check important numbers on the report.</small>
                @endif
            </div>
        @endif
    </div>
</div>
