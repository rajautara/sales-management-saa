<?php

namespace App\Livewire\Advisor;

use App\Services\Advisor\AdvisorService;
use App\Services\Advisor\FinancialSnapshotService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $period = 'this_month';

    public string $input = '';

    /** @var array<int, array{role: string, content: string}> */
    public array $messages = [];

    public string $review = '';

    public ?string $error = null;

    public function mount(): void
    {
        if (! auth()->user()?->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }
    }

    public function generateReview(
        FinancialSnapshotService $snapshots,
        AdvisorService $advisor,
    ): void {
        $this->error = null;

        try {
            $this->review = $advisor->review($snapshots->build($this->period));
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function send(
        FinancialSnapshotService $snapshots,
        AdvisorService $advisor,
    ): void {
        $question = trim($this->input);

        if ($question === '') {
            return;
        }

        $this->error = null;
        $this->messages[] = ['role' => 'user', 'content' => $question];
        $this->input = '';

        try {
            $answer = $advisor->chat($this->messages, $snapshots->build($this->period));
            $this->messages[] = ['role' => 'assistant', 'content' => $answer];
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function clearChat(): void
    {
        $this->messages = [];
        $this->error = null;
    }

    public function render()
    {
        return view('livewire.advisor.index', [
            'configured' => (bool) config('ai.enabled') && filled(config('ai.api_key')),
        ]);
    }
}
