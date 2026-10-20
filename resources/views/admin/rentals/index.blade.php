@extends('layouts.app')

@section('title', 'Retiradas e devoluções - 7 Rent')

@php
    $tabs = [
        null       => 'Todos',
        'reserved' => 'Reservados',
        'active'   => 'Em andamento',
        'late'     => 'Atrasados',
        'returned' => 'Devolvidos',
        'cancelled' => 'Cancelados',
        'expired'  => 'Expirados',
    ];
@endphp

@section('content')
    <div>

        <div class="mb-8">
            <h1 class="text-white text-3xl font-bold tracking-tight">Retiradas e devoluções</h1>
            <p class="text-sm text-[#678] mt-1">Confirme a retirada quando o cliente chegar à loja e registre a devolução do filme.</p>
        </div>

        <!-- Filtro por situação -->
        <nav class="flex flex-wrap gap-x-5 gap-y-2 border-b border-[#456] text-[12px] font-semibold uppercase tracking-[0.1em]" aria-label="Filtrar por situação">
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
                        <th class="py-3 px-4 font-semibold">Devolução</th>
                        <th class="py-3 px-4 font-semibold text-right">Valor</th>
                        <th class="py-3 px-4 font-semibold">Situação</th>
                        <th class="py-3 px-4 font-semibold text-right">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2c3440] text-sm">
                    @forelse ($rentals as $rental)
                        @php $state = $rental->displayStatus(); @endphp
                        <tr class="hover:bg-[#2c3440]/50 transition-colors">
                            <td class="py-3 px-4">
                                <p class="font-semibold text-white">{{ $rental->user->name }}</p>
                                <p class="text-xs text-[#678]">{{ $rental->user->email }}</p>
                            </td>
                            <td class="py-3 px-4 text-[#9ab]">{{ $rental->movie->title }}</td>

                            {{-- Retirada: data real ou, se for reserva, até quando o cliente pode retirar --}}
                            <td class="py-3 px-4 tabular-nums whitespace-nowrap">
                                @if ($state === 'reserved')
                                    <span class="text-[#b5e8ff]">até {{ $rental->reserved_until->format('d/m H:i') }}</span>
                                @elseif ($rental->rented_at)
                                    <span class="text-[#9ab]">{{ $rental->rented_at->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-[#456]">—</span>
                                @endif
                            </td>

                            {{-- Devolução: data devolvida, prazo ou, se ainda não retirou, só os dias --}}
                            <td class="py-3 px-4 tabular-nums whitespace-nowrap">
                                @if ($rental->returned_at)
                                    <span class="text-[#9ab]">{{ $rental->returned_at->format('d/m/Y') }}</span>
                                @elseif ($rental->due_at)
                                    <span class="{{ $state === 'late' ? 'text-[#ff9b9b]' : 'text-[#9ab]' }}">até {{ $rental->due_at->format('d/m/Y') }}</span>
                                @elseif ($state === 'reserved')
                                    <span class="text-[#678]">{{ $rental->days }} {{ $rental->days == 1 ? 'dia' : 'dias' }} após retirar</span>
                                @else
                                    <span class="text-[#456]">—</span>
                                @endif
                            </td>

                            <td class="py-3 px-4 text-right tabular-nums whitespace-nowrap {{ in_array($state, ['cancelled', 'expired']) ? 'text-[#678] line-through' : 'text-white' }}">
                                R$ {{ number_format($rental->total_price + $rental->late_fee, 2, ',', '.') }}
                                @if ($rental->late_fee > 0)
                                    <span class="block text-xs text-[#ff9b9b] no-underline">multa R$ {{ number_format($rental->late_fee, 2, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">@include('partials.rental-status', ['rental' => $rental])</td>

                            <td class="py-3 px-4 text-right">
                                @if ($state === 'reserved')
                                    <form action="{{ route('admin.rentals.pickup', $rental) }}" method="POST"
                                        onsubmit="return confirm('Confirmar a retirada de {{ addslashes($rental->movie->title) }} por {{ addslashes($rental->user->name) }}?');">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-[#53c7ff] hover:bg-[#8fdcff] text-[#14181c] text-[11px] font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors whitespace-nowrap">
                                            Confirmar retirada
                                        </button>
                                    </form>
                                @elseif ($state === 'active' || $state === 'late')
                                    <form action="{{ route('admin.rentals.return', $rental) }}" method="POST"
                                        onsubmit="return confirm('Registrar a devolução de {{ addslashes($rental->movie->title) }}?');">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-[#456] hover:bg-[#567] text-white text-[11px] font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors whitespace-nowrap">
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
                            <td colspan="7" class="py-12 text-center text-[#678] text-sm">
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
