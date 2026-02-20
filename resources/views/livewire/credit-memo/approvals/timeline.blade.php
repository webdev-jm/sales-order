<div class="card">
    <div class="card-header">
        <h3 class="card-title">APPROVAL TIMELINE</h3>
    </div>
    <div class="card-body">
        @if($timeline && $timeline->count() > 0)
            <div class="timeline timeline-inverse" style="max-height: 800px; min-height: 140px; overflow-y: auto;">
                @foreach($timeline as $date => $items)
                    <div class="time-label">
                        <span class="bg-primary text-uppercase">{{ date('M j Y', strtotime($date)) }}</span>
                    </div>

                    @foreach($items as $item)
                        @if($item instanceof App\Models\CreditMemoApproval)
                            <div>
                                <i class="fas fa-user bg-{{ $status_arr[$item->status] ?? 'secondary' }}"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="far fa-clock"></i> {{ $item->created_at->format('h:i A') }}</span>
                                    <h3 class="timeline-header {{ !empty($item->remarks) ? '' : 'border-0' }}">
                                        <a href="#">{{ $item->user->fullName() ?? 'Unknown User' }}</a>
                                        <span class="mx-2 badge badge-{{ $status_arr[$item->status] ?? 'secondary' }}">{{ strtoupper($item->status) }}</span>
                                        the RUD
                                    </h3>
                                    @if(!empty($item->remarks))
                                        <div class="timeline-body">
                                            <label class="mb-0">REMARKS:</label>
                                            <pre class="mb-0 ml-2">{{ preg_replace('/[^\S\n]+/', ' ', $item->remarks) }}</pre>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($item instanceof App\Models\CreditMemoRemarks)
                            <div>
                                <i class="fas fa-comment bg-secondary"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="far fa-clock"></i> {{ $item->created_at->format('h:i A') }}</span>
                                    <h3 class="timeline-header border-0">
                                        <a href="#">{{ $item->user->fullName() ?? 'Unknown User' }}</a> added a remark
                                    </h3>
                                    <div class="timeline-body">
                                        {{ $item->message }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                @endforeach

                <div>
                    <i class="far fa-clock bg-gray"></i>
                </div>
            </div>
        @else
            <div class="text-center text-muted py-3">
                - No approval history or remarks available -
            </div>
        @endif
    </div>
</div>
