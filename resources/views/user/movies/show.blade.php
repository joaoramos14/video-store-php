@extends('layouts.app')

@section('title', $movie->title . ' - 7 Rent')

@php
    $available = $movie->stock > 0;
    $avg = $movie->reviews_avg_rating;
    $maxCount = max($distribution->max() ?? 0, 1);
    $labelClass = 'block text-[11px] font-semibold uppercase tracking-[0.1em] text-[#9ab] mb-2';
    $sectionTitle = 'text-[12px] font-semibold uppercase tracking-[0.1em] text-[#9ab] border-b border-[#456] pb-1';
@endphp

@section('content')
    <div>
        <a href="{{ route('movies.index') }}"
            class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#678] hover:text-[#53c7ff] transition-colors">
            &larr; Catálogo
        </a>

        <!-- Duas colunas: capa e informações. A faixa de aluguel ocupa as duas. -->
        <div class="grid grid-cols-1 sm:grid-cols-[180px_1fr] gap-x-8 gap-y-8 mt-5">

            <!-- Capa -->
            <div class="w-[150px] sm:w-[180px] aspect-[2/3] rounded-[3px] overflow-hidden bg-[#2c3440] ring-1 ring-inset ring-white/15">
                @if ($movie->cover_url)
                    <img src="{{ $movie->cover_url }}" alt="Capa de {{ $movie->title }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-center text-sm text-[#678] px-4">
                        {{ $movie->title }}
                    </div>
                @endif
            </div>

            <!-- Informações -->
            <div class="min-w-0">
                <h1 class="text-white text-3xl sm:text-4xl font-bold tracking-tight leading-tight">{{ $movie->title }}</h1>

                <p class="mt-2 text-sm text-[#9ab] tabular-nums">
                    <span class="text-white">{{ $movie->year }}</span>
                    @if ($movie->genre)
                        &middot;
                        <a href="{{ route('movies.index', ['genre_id' => $movie->genre_id]) }}"
                            class="hover:text-[#53c7ff] transition-colors">{{ $movie->genre->name }}</a>
                    @endif
                    &middot; {{ $movie->duration }} min
                </p>

                @if ($movie->director)
                    <p class="mt-1 text-sm text-[#678]">Dirigido por <span class="text-[#9ab]">{{ $movie->director }}</span></p>
                @endif

                @if ($movie->synopsis)
                    <p class="mt-5 max-w-[62ch] text-[15px] leading-7 text-[#9ab]">{{ $movie->synopsis }}</p>
                @endif

                <!-- Resumo das avaliações -->
                <div class="mt-6 flex flex-wrap items-center gap-x-8 gap-y-4">
                    @if ($movie->reviews_count > 0)
                        <div class="flex items-baseline gap-3">
                            <span class="text-white text-4xl font-bold tabular-nums">{{ number_format($avg, 1, ',', '') }}</span>
                            <div>
                                @include('partials.stars', ['value' => $avg])
                                <p class="text-xs text-[#678] tabular-nums">
                                    {{ $movie->reviews_count }} {{ $movie->reviews_count == 1 ? 'avaliação' : 'avaliações' }}
                                </p>
                            </div>
                        </div>

                        <ul class="space-y-1 w-44">
                            @foreach ([5, 4, 3, 2, 1] as $star)
                                @php $count = $distribution[$star] ?? 0; @endphp
                                <li class="flex items-center gap-2 text-[11px] tabular-nums">
                                    <span class="w-2.5 text-[#9ab]">{{ $star }}</span>
                                    <span class="flex-1 h-1.5 bg-[#2c3440] rounded-[2px] overflow-hidden">
                                        <span class="block h-full bg-[#53c7ff]" style="width: {{ $count / $maxCount * 100 }}%"></span>
                                    </span>
                                    <span class="w-4 text-right text-[#678]">{{ $count }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-[#678]">Ainda sem avaliações.</p>
                    @endif
                </div>
            </div>

            <!-- Reserva: ocupa as duas colunas -->
            <section class="sm:col-span-2" aria-labelledby="alugar-titulo">
                <h2 id="alugar-titulo" class="{{ $sectionTitle }}">Reservar</h2>
                <div class="bg-[#1b2229] border-x border-b border-[#2c3440] rounded-b-[3px] p-5">

                    @if ($openRental)
                        <p class="mb-4 text-sm text-[#b5e8ff] bg-[#53c7ff]/10 border border-[#53c7ff]/30 rounded-[3px] px-3 py-2">
                            @if ($openRental->isReserved())
                                Você reservou este filme. Retire na loja até
                                <span class="tabular-nums">{{ $openRental->reserved_until->format('d/m/Y') }} às {{ $openRental->reserved_until->format('H:i') }}</span>.
                            @else
                                Você está com este filme. A devolução está prevista para
                                <span class="tabular-nums">{{ $openRental->due_at->format('d/m/Y') }}</span>.
                            @endif
                            <a href="{{ route('rentals.index') }}" class="underline underline-offset-2 hover:text-white">Ver meus aluguéis</a>
                        </p>
                    @endif

                    <div class="flex flex-col lg:flex-row lg:items-end gap-5 lg:gap-8">

                        <!-- Preço e disponibilidade -->
                        <div class="lg:min-w-[190px]">
                            <p class="text-white tabular-nums">
                                <span class="text-2xl font-bold">R$ {{ number_format($movie->daily_price, 2, ',', '.') }}</span>
                                <span class="text-sm text-[#678]">por dia</span>
                            </p>
                            <p class="mt-1 text-xs flex items-center gap-1.5 {{ $available ? 'text-[#9ab]' : 'text-[#ff9b9b]' }}">
                                <span class="inline-block w-1.5 h-1.5 rounded-full {{ $available ? 'bg-[#53c7ff]' : 'bg-[#ff5b5b]' }}"></span>
                                @if ($available)
                                    Disponível &middot; {{ $movie->stock }} {{ $movie->stock == 1 ? 'cópia' : 'cópias' }}
                                @else
                                    Indisponível no momento
                                @endif
                            </p>
                        </div>

                        @if (!$openRental && $available)
                            <form action="{{ route('rentals.store', $movie) }}" method="POST"
                                class="flex-1 flex flex-col sm:flex-row sm:items-end gap-4 sm:gap-6">
                                @csrf
                                <div class="sm:w-32">
                                    <label for="days" class="{{ $labelClass }}">Dias</label>
                                    <input type="number" name="days" id="days" min="1" max="30" step="1" required
                                        value="{{ old('days', 3) }}"
                                        class="w-full bg-[#2c3440] border border-transparent rounded-[3px] px-3 py-2.5 text-sm text-white tabular-nums focus:outline-none focus:border-[#53c7ff] focus:bg-[#14181c] transition-colors">
                                </div>

                                <p class="flex-1 text-sm text-[#9ab] sm:pb-0.5">
                                    Total: <span id="rent-total" class="text-white font-semibold tabular-nums"></span>
                                    <span class="block text-xs text-[#678]">Devolução até <span id="rent-due" class="tabular-nums"></span>, se retirar hoje</span>
                                </p>

                                <button type="submit"
                                    class="px-8 py-2.5 bg-[#53c7ff] hover:bg-[#8fdcff] text-[#14181c] text-xs font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors">
                                    Reservar
                                </button>
                            </form>
                        @elseif (!$openRental)
                            <div class="lg:ml-auto">
                                <button type="button" disabled
                                    class="px-8 py-2.5 bg-[#2c3440] text-[#678] text-xs font-bold uppercase tracking-[0.1em] rounded-[3px] cursor-not-allowed">
                                    Indisponível
                                </button>
                            </div>
                        @endif
                    </div>

                    @error('days')
                        <p class="text-[#ff7b7b] text-xs mt-3">{{ $message }}</p>
                    @enderror

                    @if (!$openRental && $available)
                        <p class="mt-4 text-xs text-[#678]">
                            Retire o filme na loja em até {{ config('rentals.reservation_hours') }} horas.
                            O prazo de devolução só começa a contar na retirada.
                        </p>
                    @endif
                </div>
            </section>
        </div>

        <!-- Avaliações dos clientes -->
        <section class="mt-10">
            <h2 id="avaliacoes" class="{{ $sectionTitle }} scroll-mt-6">O que os clientes acharam</h2>

            @forelse ($reviews as $review)
                <article class="py-5 border-b border-[#2c3440] flex gap-4">
                    <div class="shrink-0 w-9 h-9 rounded-full bg-[#2c3440] text-[#9ab] text-sm font-semibold flex items-center justify-center"
                        aria-hidden="true">
                        {{ mb_strtoupper(mb_substr($review->user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm">
                            <span class="text-white font-semibold">{{ $review->user->name }}</span>
                            <span class="ml-2">@include('partials.stars', ['value' => $review->rating])</span>
                            <span class="ml-2 text-xs text-[#678] tabular-nums">{{ $review->created_at->format('d/m/Y') }}</span>
                        </p>
                        @if ($review->comment)
                            <p class="mt-1.5 text-sm leading-relaxed text-[#9ab] whitespace-pre-line break-words">{{ $review->comment }}</p>
                        @endif
                    </div>
                </article>
            @empty
                <p class="py-8 text-sm text-[#678]">
                    Nenhuma avaliação ainda. Depois de alugar, você pode avaliar este filme em Meus aluguéis.
                </p>
            @endforelse

            @if ($reviews->hasPages())
                <div class="mt-6">{{ $reviews->links() }}</div>
            @endif
        </section>
    </div>

    <script>
        // Atualiza o total e a data de devolução conforme os dias digitados
        (function() {
            const price = @json((float) $movie->daily_price);
            const input = document.getElementById('days');
            const total = document.getElementById('rent-total');
            const due = document.getElementById('rent-due');
            if (!input) return;

            const money = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
            const date = new Intl.DateTimeFormat('pt-BR');

            function update() {
                const days = Math.min(Math.max(parseInt(input.value, 10) || 0, 0), 30);
                total.textContent = money.format(price * days);
                const end = new Date();
                end.setDate(end.getDate() + days);
                due.textContent = days > 0 ? date.format(end) : '—';
            }

            input.addEventListener('input', update);
            update();
        })();
    </script>
@endsection
