<div>
    <div class="card card-primary card-outline direct-chat direct-chat-primary">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-comments mr-1"></i> Ask about your sales orders</h3>
            <div class="card-tools">
                @if(!empty($history))
                    <button type="button" class="btn btn-tool" wire:click="newChat" wire:loading.attr="disabled" title="Start a new chat">
                        <i class="fas fa-plus mr-1"></i> New chat
                    </button>
                @endif
            </div>
        </div>

        <div class="card-body">
            <div class="direct-chat-messages ai-chat-messages" id="ai-chat-messages">
                @if(empty($history))
                    <div class="text-muted px-2 py-3">
                        <p class="mb-2">The assistant reads the sales orders of the accounts assigned to you and answers with figures from SMS. It cannot change anything.</p>
                        <p class="mb-2">Try one of these:</p>
                        @foreach($suggestions as $index => $suggestion)
                            <button type="button" class="btn btn-sm btn-outline-primary mb-1 mr-1" wire:key="suggestion-{{ $index }}"
                                    wire:click="useSuggestion({{ $index }})">{{ $suggestion }}</button>
                        @endforeach
                    </div>
                @endif

                @foreach($history as $index => $turn)
                    @if($turn['role'] === 'user')
                        <div class="direct-chat-msg right" wire:key="turn-{{ $index }}">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name float-right">You</span>
                            </div>
                            <div class="direct-chat-text ai-chat-question">{{ $turn['content'] }}</div>
                        </div>
                    @else
                        <div class="direct-chat-msg" wire:key="turn-{{ $index }}">
                            <div class="direct-chat-infos clearfix">
                                <span class="direct-chat-name float-left"><i class="fas fa-robot mr-1"></i>Assistant</span>
                            </div>
                            <div class="direct-chat-text ai-chat-answer">
                                {!! \Illuminate\Support\Str::markdown($turn['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                            </div>
                        </div>
                    @endif
                @endforeach

                <div class="direct-chat-msg" wire:loading.block wire:target="ask">
                    <div class="direct-chat-infos clearfix">
                        <span class="direct-chat-name float-left"><i class="fas fa-robot mr-1"></i>Assistant</span>
                    </div>
                    <div class="direct-chat-text text-muted">
                        <i class="fas fa-spinner fa-spin mr-1"></i> Reading your sales orders…
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            @if($error !== '')
                <div class="alert alert-warning py-2 mb-2"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $error }}</div>
            @endif
            <form wire:submit="ask">
                <div class="input-group">
                    <input type="text" class="form-control{{ $errors->has('question') ? ' is-invalid' : '' }}" id="ai-question"
                           placeholder="Ask about orders, sales or products in your accounts" maxlength="1000" autocomplete="off"
                           wire:model="question" wire:loading.attr="disabled" wire:target="ask">
                    <span class="input-group-append">
                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled" wire:target="ask">
                            <span wire:loading.remove wire:target="ask"><i class="fas fa-paper-plane mr-1"></i> Ask</span>
                            <span wire:loading wire:target="ask"><i class="fas fa-spinner fa-spin mr-1"></i> Asking…</span>
                        </button>
                    </span>
                </div>
                @error('question') <small class="text-danger">{{ $message }}</small> @enderror
            </form>
            <small class="text-muted d-block mt-1">Answers come from AI and use SMS figures. Check important numbers on the reports.</small>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', function () {
            var scrollToEnd = function () {
                var box = document.getElementById('ai-chat-messages');
                if (box) { box.scrollTop = box.scrollHeight; }
            };
            scrollToEnd();
            Livewire.hook('morphed', scrollToEnd);
        });
    </script>
</div>
