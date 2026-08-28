<div {{ $attributes }}>
    <div class="md:hidden divide-y divide-gray-200">
        {{ $cards }}
    </div>

    <div class="hidden md:block overflow-x-auto touch-scroll">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="px-4 py-4 md:px-6 overflow-x-auto touch-scroll">
            {{ $footer }}
        </div>
    @endisset
</div>
