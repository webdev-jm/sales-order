<?php

namespace App\Services\Ai\Agents;

use App\Models\User;
use App\Services\Ai\Concerns\UsesAiSettings;
use App\Services\Ai\SalesFigures;
use App\Services\Ai\Tools\FindSalesOrders;
use App\Services\Ai\Tools\ListMyAccounts;
use App\Services\Ai\Tools\SalesByBrand;
use App\Services\Ai\Tools\SalesOrderDetail;
use App\Services\Ai\Tools\SalesSummary;
use App\Services\Ai\Tools\TopProducts;
use Laravel\Ai\Attributes\CacheToolDefinitions;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasProviderOptions;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\Promptable;

/**
 * Answers a user's questions about the sales orders of their own accounts. It reads figures only
 * through its tools, which are limited to the accounts assigned to the user.
 *
 * Every step of a tool loop, and every follow-up question, sends the tools, the instructions and the conversation
 * again. Providers bill a repeated opening of a request at a fraction of the input price, so the tools and the
 * fixed rules come first and the parts that change (the user, the date) come last. CacheToolDefinitions asks
 * Anthropic to cache the tools; Gemini and OpenAI reuse the shared opening on their own.
 */
#[MaxSteps(6)]
#[CacheToolDefinitions]
class SalesAssistant implements Agent, Conversational, HasProviderOptions, HasTools
{
    use Promptable;
    use UsesAiSettings;

    /**
     * @param array<int, int> $accountIds The accounts assigned to the user
     * @param array<int, array{role: string, content: string}> $history Earlier turns, oldest first
     */
    public function __construct(private User $user, private array $accountIds, private array $history = [])
    {
    }

    public function instructions(): string
    {
        return implode("\n", [
            'You are the sales assistant of BEVI\'s Sales Management System (SMS). BEVI supplies consumer goods to retail accounts in the Philippines.',
            'You help the signed-in user understand the sales orders of the customer accounts assigned to them.',
            '',
            'Rules:',
            '- Use the tools for every number. Never estimate, invent or remember figures; if a tool cannot answer, say so.',
            '- Say which period and accounts your answer covers. When the user gives no period, use this month so far and say so.',
            '- Sales totals count orders that are finalized and uploaded to SYSPRO. Net sales are after discounts; gross sales are before.',
            '- Amounts are in Philippine pesos; write them like PHP 1,234.50.',
            '- You can only read the user\'s own accounts. If they ask about another account, say it is not assigned to them.',
            '- You cannot change anything. For creating, editing or cancelling orders, point them to the Sales Orders or SO Cancel Requests pages.',
            '- Only answer questions about SMS sales data. Decline anything else politely in one sentence.',
            '- Keep answers short: a sentence or two, then a small list or table when it helps.',
            '',
            'The signed-in user is ' . $this->user->fullName() . '. Today is ' . now()->format('l, F j, Y') . '.',
        ]);
    }

    public function messages(): iterable
    {
        return array_map(
            fn (array $turn) => $turn['role'] === 'assistant' ? new AssistantMessage($turn['content']) : new UserMessage($turn['content']),
            $this->history
        );
    }

    public function tools(): iterable
    {
        $figures = app(SalesFigures::class);

        return [
            new ListMyAccounts($figures, $this->accountIds),
            new SalesSummary($figures, $this->accountIds),
            new TopProducts($figures, $this->accountIds),
            new SalesByBrand($figures, $this->accountIds),
            new FindSalesOrders($figures, $this->accountIds),
            new SalesOrderDetail($figures, $this->accountIds),
        ];
    }

    public function maxTokens(): int
    {
        return 2000;
    }
}
