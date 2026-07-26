@props(['href' => null, 'active' => false, 'built' => true])

@if ($built)
    <a href="{{ $href }}"
        {{ $attributes->merge([
            'class' => 'flex items-center justify-between rounded-md px-3 py-2 text-sm font-medium transition '
                . ($active
                    ? 'bg-slate-800 text-white'
                    : 'text-slate-300 hover:bg-slate-800/60 hover:text-white'),
        ]) }}
    >
        <span>{{ $slot }}</span>
    </a>
@else
    <span
        {{ $attributes->merge([
            'class' => 'flex items-center justify-between rounded-md px-3 py-2 text-sm font-medium text-slate-500 cursor-default select-none',
        ]) }}
        title="Not migrated yet"
    >
        <span>{{ $slot }}</span>
        <span class="ms-2 shrink-0 rounded-full bg-slate-800 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Soon</span>
    </span>
@endif
