@php
    $badge = ['high' => 'badge-success', 'medium' => 'badge-warning', 'low' => 'badge-secondary'][$confidence] ?? 'badge-secondary';
@endphp
<span class="badge {{ $badge }} text-uppercase">{{ $confidence }}</span>
