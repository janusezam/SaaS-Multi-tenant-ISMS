@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center px-2 pt-1 border-b-2 border-cyan-400 text-sm font-medium leading-5 text-cyan-200 focus:outline-none focus:border-cyan-300 transition duration-150 ease-in-out'
            : 'isms-nav-link inline-flex items-center px-2 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 hover:border-cyan-400/60 focus:outline-none focus:border-cyan-400/60 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
