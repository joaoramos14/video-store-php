{{-- Estrelas de 0 a 5. Uso: @include('partials.stars', ['value' => 4.3]) --}}
@php $filled = (int) round($value ?? 0); @endphp
<span class="text-[#53c7ff] tracking-tight whitespace-nowrap" role="img"
    aria-label="Nota {{ $filled }} de 5">{{ str_repeat('★', $filled) }}<span
        class="text-[#456]">{{ str_repeat('★', 5 - $filled) }}</span></span>
