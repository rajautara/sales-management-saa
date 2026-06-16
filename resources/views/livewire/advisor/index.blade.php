<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">AI Business Advisor</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">
        @unless ($configured)
            <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-lg text-sm">
                The AI advisor is not configured yet. Set <code class="font-mono">AI_ENABLED=true</code>,
                <code class="font-mono">AI_API_KEY</code> and <code class="font-mono">AI_MODEL</code> (and optionally
                <code class="font-mono">AI_BASE_URL</code> for a non-OpenAI provider) in your environment.
            </div>
        @endunless

        @if ($error)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                {{ $error }}
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-3">
            <label class="text-sm font-medium text-gray-700">Period</label>
            <select wire:model.live="period" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                <option value="this_month">This Month</option>
                <option value="last_month">Last Month</option>
                <option value="this_quarter">This Quarter</option>
                <option value="this_year">This Year</option>
            </select>
            <p class="text-xs text-gray-500">Only aggregated figures are sent — no customer names or PII.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Financial Review --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Financial Review</h3>
                    <button type="button" wire:click="generateReview" wire:loading.attr="disabled" @disabled(! $configured)
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="generateReview">Generate Review</span>
                        <span wire:loading wire:target="generateReview">Generating…</span>
                    </button>
                </div>

                @if ($review)
                    <div class="prose prose-sm max-w-none text-gray-800">
                        {!! \Illuminate\Support\Str::markdown($review, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                    </div>
                @else
                    <p class="text-sm text-gray-500">Generate an AI assessment of this period's financial health, cash flow and top action items.</p>
                @endif
            </div>

            {{-- Chat --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 flex flex-col" style="min-height: 28rem;">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Ask the Advisor</h3>
                    @if ($messages)
                        <button type="button" wire:click="clearChat" class="text-xs text-gray-500 hover:text-gray-700">Clear</button>
                    @endif
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto mb-4" style="max-height: 24rem;">
                    @forelse ($messages as $message)
                        <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[85%] rounded-lg px-3 py-2 text-sm {{ $message['role'] === 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                                @if ($message['role'] === 'assistant')
                                    <div class="prose prose-sm max-w-none">
                                        {!! \Illuminate\Support\Str::markdown($message['content'], ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                                    </div>
                                @else
                                    {{ $message['content'] }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Ask anything about your finances — e.g. <em>"Macam mana cashflow saya bulan ni?"</em> or <em>"Kenapa untung turun?"</em></p>
                    @endforelse

                    {{-- Live-streamed answer (cleared once the full message is appended) --}}
                    <div wire:loading.flex wire:target="send" class="justify-start">
                        <div class="max-w-[85%] bg-gray-100 text-gray-800 rounded-lg px-3 py-2 text-sm whitespace-pre-wrap">
                            <span wire:stream="answer"></span><span class="animate-pulse">▍</span>
                        </div>
                    </div>
                </div>

                <form wire:submit="send" class="flex items-center gap-2">
                    <input type="text" wire:model="input" placeholder="Type your question…" @disabled(! $configured)
                        class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm disabled:bg-gray-50">
                    <button type="submit" wire:loading.attr="disabled" wire:target="send" @disabled(! $configured)
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition disabled:opacity-50">
                        Send
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
