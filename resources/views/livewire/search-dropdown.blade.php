<div class="form-group position-relative">
    @if($label)
        <label>{{ $label }}</label>
    @endif

    <div class="input-group">
        <input
            type="text"
            class="form-control"
            placeholder="{{ $placeholder }}"
            wire:model.debounce.300ms="search"
            wire:focus="$set('showDropdown', true)"
            autocomplete="off"
        >

        @if($search)
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" wire:click="$set('search', '')">
                    ×
                </button>
            </div>
        @endif
    </div>

    @if($showDropdown && count($results) > 0)
        <div class="list-group position-absolute w-100 shadow" style="z-index: 1000; max-height: 250px; overflow-y: auto;">
            @foreach($results as $result)
                <button
                    type="button"
                    class="list-group-item list-group-item-action"
                    wire:click="selectItem('{{ $result[$valueField] }}')"
                >
                    {{ $this->getDisplayValue($result) }}
                </button>
            @endforeach
        </div>
    @elseif($showDropdown && strlen($search) >= 2)
         <div class="list-group position-absolute w-100 shadow" style="z-index: 1000;">
            <div class="list-group-item text-muted">No results found.</div>
         </div>
    @endif
</div>
