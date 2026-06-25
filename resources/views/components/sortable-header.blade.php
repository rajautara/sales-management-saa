@props([
    'field',
    'current',
    'direction',
    'align' => 'left',
])

<button type="button" wire:click="sortBy('{{ $field }}')"
        {{ $attributes->merge(['class' => 'group inline-flex items-center gap-1 text-xs font-medium text-gray-500 uppercase tracking-wider hover:text-gray-700 focus:outline-none ' . ($align === 'right' ? 'flex-row-reverse' : '')]) }}>
    <span>{{ $slot }}</span>
    @if ($current === $field)
        @if ($direction === 'asc')
            <svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" /></svg>
        @else
            <svg class="w-3 h-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
        @endif
    @else
        <svg class="w-3 h-3 opacity-0 group-hover:opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
    @endif
</button>
