{{-- Selo do estado do aluguel. Uso: @include('partials.rental-status', ['rental' => $rental]) --}}
@php
    $states = [
        'active' => ['Em andamento', 'bg-[#53c7ff]/15 text-[#53c7ff]'],
        'late' => ['Atrasado', 'bg-[#ff5b5b]/15 text-[#ff9b9b]'],
        'returned' => ['Devolvido', 'bg-[#2c3440] text-[#9ab]'],
    ];
    [$label, $classes] = $states[$rental->displayStatus()];
@endphp
<span
    class="inline-block text-[11px] font-semibold uppercase tracking-[0.1em] px-2.5 py-0.5 rounded-[3px] whitespace-nowrap {{ $classes }}">{{ $label }}</span>
