<div>
    <input
        type="text"
        class="form-control"
        placeholder="Search Branch..."
        wire:model="query"
        wire:keydown.escape="resetData"
        wire:keydown.tab="resetData"
        wire:keydown.arrow-up="decrementHighlight"
        wire:keydown.arrow-down="incrementHighlight"
        wire:keydown.enter="selectContact"
    />
 
    <div wire:loading class="absolute z-10 w-full bg-white rounded-t-none shadow-lg list-group">
        <div class="list-item">Searching...</div>
    </div>
 
    @if(!empty($query))
        <div class="fixed top-0 bottom-0 left-0 right-0" wire:click="reset"></div>
 
        <div class="absolute z-10 w-full bg-white rounded-t-none shadow-lg list-group">
            @if(!empty($branches))
                @foreach($branches as $i => $branch)
                    <a
                        href="#"
                        class="list-item {{ $highlightIndex === $i ? 'bg-info' : '' }}"
                    >{{ $branch['branch_name'] }}</a>
                @endforeach
            @else
                <div class="list-item">No results!</div>
            @endif
        </div>
    @endif
</div>
