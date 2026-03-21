@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-cyan-400 text-start text-base font-medium text-cyan-200 bg-cyan-500/10 focus:outline-none focus:text-cyan-100 focus:bg-cyan-500/15 focus:border-cyan-300 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-300 hover:text-white hover:bg-white/5 hover:border-cyan-300/60 focus:outline-none focus:text-white focus:bg-white/5 focus:border-cyan-300/60 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
