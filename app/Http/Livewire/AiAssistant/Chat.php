<?php

namespace App\Http\Livewire\AiAssistant;

use App\Exceptions\AiUnavailableException;
use App\Models\AiUsageLog;
use App\Services\Ai\AgentRunner;
use App\Services\Ai\Agents\SalesAssistant;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * The AI sales assistant chat. The conversation is kept in the session, so it survives a page
 * reload and is gone when the user signs out or starts a new chat.
 */
class Chat extends Component
{
    private const SESSION_KEY = 'ai_assistant.history';

    /** Earlier turns sent with each question, so follow-up questions keep their context. */
    private const HISTORY_TURNS = 20;

    public $question = '';

    /** @var array<int, array{role: string, content: string}> */
    public $history = [];

    public $error = '';

    /** @var array<int, string> */
    public $suggestions = [
        'How much did my accounts order this month?',
        'What are my top 5 products this month by quantity?',
        'Which of my orders are not uploaded to SYSPRO yet?',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('ai assistant access'), 403);

        $this->history = (array) Session::get(self::SESSION_KEY, []);
    }

    public function ask(AgentRunner $runner): void
    {
        abort_unless(auth()->user()->can('ai assistant access'), 403);

        $this->validate(
            ['question' => 'required|string|max:1000'],
            ['question.required' => 'Type a question first.', 'question.max' => 'Keep the question under 1,000 characters.']
        );

        $this->error = '';
        $user = auth()->user();
        $accountIds = $user->accounts()->pluck('accounts.id')->map(fn ($id): int => (int) $id)->all();

        if ($accountIds === []) {
            $this->error = 'No accounts are assigned to you yet, so there are no sales orders to ask about.';

            return;
        }

        $question = trim($this->question);
        $previous = \array_slice($this->history, -self::HISTORY_TURNS);

        try {
            $response = $runner->prompt(
                new SalesAssistant($user, $accountIds, $previous),
                $question,
                AiUsageLog::FEATURE_ASSISTANT,
                $user,
                reference: Str::limit($question, 120),
            );
        } catch (AiUnavailableException $e) {
            $this->error = $e->getMessage();

            return;
        }

        $answer = trim((string) $response->text);

        $this->history[] = ['role' => 'user', 'content' => $question];
        $this->history[] = ['role' => 'assistant', 'content' => $answer !== '' ? $answer : 'I could not find an answer to that. Try asking another way.'];
        $this->question = '';

        Session::put(self::SESSION_KEY, $this->history);
    }

    public function useSuggestion(int $index): void
    {
        $this->question = $this->suggestions[$index] ?? '';
    }

    public function newChat(): void
    {
        $this->reset('history', 'question', 'error');
        Session::forget(self::SESSION_KEY);
    }

    public function render(): View
    {
        return view('livewire.ai-assistant.chat');
    }
}
