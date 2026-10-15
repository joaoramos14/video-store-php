@extends('layouts.app')

@section('title', 'Meus aluguéis - 7 Rent')

@php
    $coverUrl = fn($cover) => !$cover ? null : (str_starts_with($cover, 'http') ? $cover : "https://image.tmdb.org/t/p/w154{$cover}");
@endphp

@section('content')
    <div>

        <div class="mb-8">
            <h1 class="text-white text-3xl font-bold tracking-tight">Meus aluguéis</h1>
            <p class="text-sm text-[#678] mt-1">Reserve pelo site, retire e devolva na loja e avalie o que você assistiu.</p>
        </div>

        <div class="flex items-baseline justify-between border-b border-[#456] pb-1">
            <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#9ab]">Histórico</h2>
            <span class="text-[11px] text-[#678] tabular-nums">
                {{ $rentals->total() }} {{ $rentals->total() == 1 ? 'aluguel' : 'aluguéis' }}
            </span>
        </div>

        @if ($rentals->isEmpty())
            <div class="py-16 text-center text-sm text-[#678]">
                Você ainda não reservou nenhum filme.
                <a href="{{ route('movies.index') }}"
                    class="text-[#53c7ff] hover:text-[#8fdcff] underline underline-offset-2 ml-1">Ver o catálogo</a>
            </div>
        @else
            <ul class="divide-y divide-[#2c3440] bg-[#1b2229] border-x border-b border-[#2c3440] rounded-b-[3px]">
                @foreach ($rentals as $rental)
                    @php
                        $state = $rental->displayStatus();
                        $bag = 'review_' . $rental->id;
                        $hasErrors = $errors->hasBag($bag);
                    @endphp
                    <li class="p-5">
                        <div class="flex gap-4">
                            <!-- Capa -->
                            <a href="{{ route('movies.show', $rental->movie) }}" class="shrink-0">
                                @if ($coverUrl($rental->movie->cover))
                                    <img src="{{ $coverUrl($rental->movie->cover) }}" alt="Capa de {{ $rental->movie->title }}"
                                        loading="lazy"
                                        class="w-16 aspect-[2/3] object-cover rounded-[3px] ring-1 ring-inset ring-white/15 bg-[#2c3440] {{ in_array($state, ['cancelled', 'expired']) ? 'opacity-50 grayscale' : '' }}">
                                @else
                                    <div class="w-16 aspect-[2/3] rounded-[3px] ring-1 ring-inset ring-white/10 bg-[#2c3440]"></div>
                                @endif
                            </a>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <h3 class="text-white font-semibold leading-snug">
                                        <a href="{{ route('movies.show', $rental->movie) }}"
                                            class="hover:text-[#53c7ff] transition-colors">{{ $rental->movie->title }}</a>
                                        <span class="text-[#678] font-normal tabular-nums">{{ $rental->movie->year }}</span>
                                    </h3>
                                    @include('partials.rental-status', ['rental' => $rental])
                                </div>

                                <dl class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-x-6 gap-y-3 text-sm">
                                    <div>
                                        <dt class="text-[11px] uppercase tracking-[0.1em] text-[#678]">Reservado em</dt>
                                        <dd class="text-[#9ab] tabular-nums">{{ $rental->created_at->format('d/m/Y') }}</dd>
                                    </div>

                                    <div>
                                        @if ($state === 'reserved')
                                            <dt class="text-[11px] uppercase tracking-[0.1em] text-[#678]">Retirar até</dt>
                                            <dd class="text-[#b5e8ff] tabular-nums">
                                                {{ $rental->reserved_until->format('d/m/Y') }}
                                                <span class="block text-xs text-[#678]">às {{ $rental->reserved_until->format('H:i') }}</span>
                                            </dd>
                                        @else
                                            <dt class="text-[11px] uppercase tracking-[0.1em] text-[#678]">Retirado em</dt>
                                            <dd class="text-[#9ab] tabular-nums">{{ $rental->rented_at?->format('d/m/Y') ?? '—' }}</dd>
                                        @endif
                                    </div>

                                    <div>
                                        @if ($rental->returned_at)
                                            <dt class="text-[11px] uppercase tracking-[0.1em] text-[#678]">Devolvido em</dt>
                                            <dd class="text-[#9ab] tabular-nums">{{ $rental->returned_at->format('d/m/Y') }}</dd>
                                        @elseif ($rental->due_at)
                                            <dt class="text-[11px] uppercase tracking-[0.1em] text-[#678]">Devolver até</dt>
                                            <dd class="tabular-nums {{ $state === 'late' ? 'text-[#ff9b9b]' : 'text-[#9ab]' }}">
                                                {{ $rental->due_at->format('d/m/Y') }}
                                                <span class="text-[#678]">({{ $rental->days }} {{ $rental->days == 1 ? 'dia' : 'dias' }})</span>
                                            </dd>
                                        @else
                                            <dt class="text-[11px] uppercase tracking-[0.1em] text-[#678]">Prazo</dt>
                                            <dd class="text-[#9ab] tabular-nums">
                                                {{ $rental->days }} {{ $rental->days == 1 ? 'dia' : 'dias' }}
                                                @if ($state === 'reserved')
                                                    <span class="block text-xs text-[#678]">a partir da retirada</span>
                                                @endif
                                            </dd>
                                        @endif
                                    </div>

                                    <div>
                                        <dt class="text-[11px] uppercase tracking-[0.1em] text-[#678]">Valor</dt>
                                        <dd class="tabular-nums {{ in_array($state, ['cancelled', 'expired']) ? 'text-[#678] line-through' : 'text-white' }}">
                                            R$ {{ number_format($rental->total_price + $rental->late_fee, 2, ',', '.') }}
                                            @if ($rental->late_fee > 0)
                                                <span class="block text-xs text-[#ff9b9b] no-underline">inclui multa de R$ {{ number_format($rental->late_fee, 2, ',', '.') }}</span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>

                                {{-- Avisos e ações de acordo com a situação --}}
                                @if ($state === 'reserved')
                                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                                        <p class="text-xs text-[#b5e8ff]">
                                            Retire o filme na loja dentro do prazo. Depois disso a reserva expira e a cópia volta para o estoque.
                                        </p>
                                        <form action="{{ route('rentals.cancel', $rental) }}" method="POST"
                                            onsubmit="return confirm('Cancelar esta reserva?');">
                                            @csrf
                                            <button type="submit"
                                                class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#678] hover:text-[#ff7b7b] transition-colors">
                                                Cancelar reserva
                                            </button>
                                        </form>
                                    </div>
                                @elseif ($state === 'expired')
                                    <p class="mt-4 text-xs text-[#678]">
                                        O prazo de retirada terminou e a reserva expirou. Você pode reservar o filme de novo.
                                    </p>
                                @elseif ($state === 'late')
                                    <p class="mt-4 text-xs text-[#ff9b9b]">
                                        O prazo venceu. Devolva o filme na loja para evitar mais dias de multa.
                                    </p>
                                @endif

                                {{-- Avaliação: só para filmes que o cliente chegou a retirar --}}
                                @if ($rental->wasPickedUp())
                                    <div class="mt-4 pt-4 border-t border-[#2c3440]">
                                        @if ($rental->review)
                                            <p class="text-sm">
                                                <span class="text-[#678]">Sua avaliação</span>
                                                <span class="ml-2">@include('partials.stars', ['value' => $rental->review->rating])</span>
                                            </p>
                                            @if ($rental->review->comment)
                                                <p class="mt-1 text-sm text-[#9ab] whitespace-pre-line break-words">{{ $rental->review->comment }}</p>
                                            @endif
                                        @else
                                            <details @if ($hasErrors) open @endif class="group">
                                                <summary
                                                    class="cursor-pointer list-none inline-block text-[11px] font-semibold uppercase tracking-[0.1em] text-[#53c7ff] hover:text-[#8fdcff] transition-colors">
                                                    Avaliar este filme
                                                </summary>

                                                <form action="{{ route('reviews.store', $rental) }}" method="POST" class="mt-4 space-y-4 max-w-lg">
                                                    @csrf

                                                    <fieldset>
                                                        <legend class="block text-[11px] font-semibold uppercase tracking-[0.1em] text-[#9ab] mb-2">Sua nota</legend>
                                                        {{-- As estrelas ficam em ordem inversa no HTML para que, ao marcar uma, as anteriores também acendam --}}
                                                        <div class="flex flex-row-reverse justify-end w-fit text-3xl leading-none">
                                                            @foreach ([5, 4, 3, 2, 1] as $n)
                                                                <input type="radio" name="rating" value="{{ $n }}"
                                                                    id="star-{{ $rental->id }}-{{ $n }}" class="peer sr-only" required>
                                                                <label for="star-{{ $rental->id }}-{{ $n }}"
                                                                    title="{{ $n }} {{ $n == 1 ? 'estrela' : 'estrelas' }}"
                                                                    class="cursor-pointer px-0.5 text-[#456] transition-colors peer-checked:text-[#53c7ff] peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-[#53c7ff] hover:text-[#8fdcff] [&:hover~label]:text-[#8fdcff]">
                                                                    <span aria-hidden="true">★</span>
                                                                    <span class="sr-only">{{ $n }} {{ $n == 1 ? 'estrela' : 'estrelas' }}</span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                        @error('rating', $bag)
                                                            <p class="text-[#ff7b7b] text-xs mt-2">{{ $message }}</p>
                                                        @enderror
                                                    </fieldset>

                                                    <div>
                                                        <label for="comment-{{ $rental->id }}"
                                                            class="block text-[11px] font-semibold uppercase tracking-[0.1em] text-[#9ab] mb-2">Comentário (opcional)</label>
                                                        <textarea name="comment" id="comment-{{ $rental->id }}" rows="3" maxlength="1000"
                                                            class="w-full bg-[#2c3440] border border-transparent rounded-[3px] px-3 py-2.5 text-sm text-white placeholder-[#678] focus:outline-none focus:border-[#53c7ff] focus:bg-[#14181c] transition-colors resize-y">{{ $hasErrors ? old('comment') : '' }}</textarea>
                                                        @error('comment', $bag)
                                                            <p class="text-[#ff7b7b] text-xs mt-2">{{ $message }}</p>
                                                        @enderror
                                                    </div>

                                                    <button type="submit"
                                                        class="px-5 py-2 bg-[#53c7ff] hover:bg-[#8fdcff] text-[#14181c] text-xs font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors">
                                                        Enviar avaliação
                                                    </button>
                                                </form>
                                            </details>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            @if ($rentals->hasPages())
                <div class="mt-10">{{ $rentals->links() }}</div>
            @endif
        @endif

    </div>
@endsection
