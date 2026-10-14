@extends('layouts.app')

@section('title', 'Devoluções - 7 Rent')

@php
    $tabs = [
        null => 'Todos',
        'active' => 'Em andamento',
        'late' => 'Atrasados',
        'returned' => 'Devolvidos',
    ];
@endphp

@section('content')
    <div>

        <div class="mb-8">
            <h1 class="text-white text-3xl font-bold tracking-tight">Devoluções</h1>
            <p class="text-sm text-[#678] mt-1">Acompanhe os aluguéis e registre a devolução dos filmes.</p>
        </div>

        <!-- Filtro por situação -->
        <nav class="flex gap-5 border-b border-[#456] mb-0 text-[12px] font-semibold uppercase tracking-[0.1em]"
            aria-label="Filtrar por situação">
            @foreach ($tabs as $key => $label)
                @php $current = ($status ?? null) === $key; @endphp
                <a href="{{ route('admin.rentals.index', $key ? ['status' => $key] : []) }}"
                    @if ($current) aria-current="page" @endif
                    class="pb-1 -mb-px border-b-2 transition-colors {{ $current ? 'text-white border-[#53c7ff]' : 'text-[#9ab] border-transparent hover:text-white' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="bg-[#1b2229] border-x border-b border-[#2c3440] rounded-b-[3px] overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#2c3440] text-[11px] uppercase tracking-[0.1em] text-[#678]">
                        <th class="py-3 px-4 font-semibold">Cliente</th>
                        <th class="py-3 px-4 font-semibold">Filme</th>
                        <th class="py-3 px-4 font-semibold">Retirada</th>
                        <th class="py-3 px-4 font-semibold">Prazo</th>
                        <th class="py-3 px-4 font-semibold">Devolução</th>
                        <th class="py-3 px-4 font-semibold text-right">Valor</th>
                        <th class="py-3 px-4 font-semibold">Situação</th>
                        <th class="py-3 px-4 font-semibold text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2c3440] text-sm">
                    @forelse ($rentals as $rental)
                        <tr class="hover:bg-[#2c3440]/50 transition-colors">
                            <td class="py-3 px-4">
                                <p class="font-semibold text-white">{{ $rental->user->name }}</p>
                                <p class="text-xs text-[#678]">{{ $rental->user->email }}</p>
                            </td>
                            <td class="py-3 px-4 text-[#9ab]">{{ $rental->movie->title }}</td>
                            <td class="py-3 px-4 text-[#9ab] tabular-nums whitespace-nowrap">
                                {{ $rental->rented_at->format('d/m/Y') }}</td>
                            <td
                                class="py-3 px-4 tabular-nums whitespace-nowrap {{ $rental->displayStatus() === 'late' ? 'text-[#ff9b9b]' : 'text-[#9ab]' }}">
                                {{ $rental->due_at->format('d/m/Y') }}
                            </td>
                            <td class="py-3 px-4 text-[#9ab] tabular-nums whitespace-nowrap">
                                {{ $rental->returned_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="py-3 px-4 text-right text-white tabular-nums whitespace-nowrap">
                                R$ {{ number_format($rental->total_price + $rental->late_fee, 2, ',', '.') }}
                                @if ($rental->late_fee > 0)
                                    <span class="block text-xs text-[#ff9b9b]">multa R$
                                        {{ number_format($rental->late_fee, 2, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">@include('partials.rental-status', ['rental' => $rental])</td>
                            <td class="py-3 px-4 text-right">
                                @if ($rental->status !== 'returned')
                                    <form action="{{ route('admin.rentals.return', $rental) }}" method="POST"
                                        onsubmit="return confirm('Registrar a devolução de {{ addslashes($rental->movie->title) }}?');">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-[#53c7ff] hover:bg-[#8fdcff] text-[#14181c] text-[11px] font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors whitespace-nowrap">
                                            Registrar devolução
                                        </button>
                                    </form>
                                @else
                                    <span class="text-[#456]">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-[#678] text-sm">
                                Nenhum aluguel {{ $status ? 'nesta situação' : 'registrado' }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($rentals->hasPages())
                <div class="p-4 border-t border-[#2c3440]">{{ $rentals->links() }}</div>
            @endif
        </div>

    </div>
@endsection
