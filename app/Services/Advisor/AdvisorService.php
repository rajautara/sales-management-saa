<?php

namespace App\Services\Advisor;

/**
 * Orchestrates the AI business advisor: turns an aggregated financial snapshot
 * into a written review or an interactive answer, via an OpenAI-compatible
 * chat endpoint.
 */
class AdvisorService
{
    public function __construct(
        protected OpenAiClient $client = new OpenAiClient,
    ) {}

    /**
     * Generate a structured monthly-style financial review (markdown).
     *
     * @param  array<string, mixed>  $snapshot
     */
    public function review(array $snapshot): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user', 'content' => $this->reviewInstruction()."\n\n".$this->snapshotBlock($snapshot)],
        ];

        return $this->client->chat($messages);
    }

    /**
     * Answer a free-form question grounded in the snapshot.
     *
     * @param  array<int, array{role: string, content: string}>  $history  prior chat turns (user/assistant)
     * @param  array<string, mixed>  $snapshot
     */
    public function chat(array $history, array $snapshot): string
    {
        return $this->client->chat($this->chatMessages($history, $snapshot));
    }

    /**
     * Streaming variant of chat(): invokes $onDelta per chunk, returns full text.
     *
     * @param  array<int, array{role: string, content: string}>  $history
     * @param  array<string, mixed>  $snapshot
     * @param  callable(string): void  $onDelta
     */
    public function streamChat(array $history, array $snapshot, callable $onDelta): string
    {
        return $this->client->streamChat($this->chatMessages($history, $snapshot), $onDelta);
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     * @param  array<string, mixed>  $snapshot
     * @return array<int, array{role: string, content: string}>
     */
    protected function chatMessages(array $history, array $snapshot): array
    {
        return [
            ['role' => 'system', 'content' => $this->systemPrompt()."\n\n".$this->snapshotBlock($snapshot)],
            ...array_map(fn ($m) => ['role' => $m['role'], 'content' => $m['content']], $history),
        ];
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
        You are a financial advisor for a small or medium business in Malaysia. The
        reporting currency is the Malaysian Ringgit (MYR) and you understand local
        context (SST, LHDN e-Invoice / MyInvois, cash-flow pressures faced by SMEs).

        Rules:
        - Base every figure strictly on the FINANCIAL SNAPSHOT provided. Never invent
          numbers. If the snapshot lacks something, say so plainly.
        - Reply in the same language the user writes in (Bahasa Melayu or English).
        - Be concise and practical. Prefer specific, actionable recommendations over
          generic advice. Quantify impact where the data allows.
        - Use the currency code from the snapshot when stating amounts.
        - The data is anonymised (no customer names). Refer to debtors generically.
        PROMPT;
    }

    protected function reviewInstruction(): string
    {
        return <<<'PROMPT'
        Write a financial review in markdown with these sections (use the user's
        likely language — default to Bahasa Melayu):

        1. **Ringkasan Kesihatan** — overall health in 2-3 sentences + a simple rating.
        2. **Keuntungan** — sales, profit, margin, and the change vs the previous period.
        3. **Aliran Tunai & Hutang** — outstanding receivables and aging; flag risks.
        4. **Inventori** — low-stock alerts worth acting on.
        5. **3 Cadangan Tindakan** — the three highest-impact next steps, ranked.

        Keep it tight — this is a busy owner's quick read.
        PROMPT;
    }

    /**
     * @param  array<string, mixed>  $snapshot
     */
    protected function snapshotBlock(array $snapshot): string
    {
        $json = json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return "FINANCIAL SNAPSHOT (aggregated, no PII):\n```json\n{$json}\n```";
    }
}
