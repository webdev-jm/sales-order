<div>
    <div class="card card-primary card-outline direct-chat direct-chat-primary">
        <div class="card-header">
            <h3 class="card-title">REMARKS</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="direct-chat-messages">
                @foreach($remarks as $remark)
                    @if($remark->user_id == auth()->user()->id)
                        <div class="direct-chat-msg right">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name float-right">{{ $remark->user->fullName() }}</span>
                                <span class="direct-chat-timestamp float-left">{{ date('j F Y, g:i a', strtotime($remark->created_at)) }}</span>
                            </div>
                            <img class="direct-chat-img" src="{{ asset('/images/sales-order-logo2.png') }}" alt="Message User Image">
                            <div class="direct-chat-text">
                                {{ $remark->message }}
                            </div>
                        </div>
                    @else
                        <div class="direct-chat-msg">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name float-left">{{ $remark->user->fullName() }}</span>
                                <span class="direct-chat-timestamp float-right">{{ date('j F Y, g:i a', strtotime($remark->created_at)) }}</span>
                            </div>
                            <img class="direct-chat-img" src="{{ asset('/images/sales-order-logo2.png') }}" alt="Message User Image">
                            <div class="direct-chat-text">
                                {{ $remark->message }}
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <div class="card-footer">
            <form action="#" method="post">
                <div class="input-group">
                    <input type="text" placeholder="Type Remarks ..." class="form-control" wire:model="message">
                    <span class="input-group-append">
                        <button type="submit" class="btn btn-primary" wire:click.prevent="saveRemarks">ADD</button>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>
