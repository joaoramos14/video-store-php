@extends('layouts.app')

@section('title', 'Catálogo - 7 Rent')

@php
    $hasFilters = request()->filled('title') || request()->filled('genre_id') || request()->filled('year');
    $fieldClass = 'w-full bg-[#2c3440] border border-transparent rounded-[3px] px-3 py-2 text-sm text-white placeholder-[#678] focus:outline-none focus:border-[#53c7ff] focus:bg-[#14181c] transition-colors';
    $labelClass = 'block text-[11px] font-semibold uppercase tracking-[0.1em] text-[#9ab] mb-1.5';
@endphp

@section('content')
    <div>

        <div class="mb-8">
            <h1 class="text-white text-3xl font-bold tracking-tight">Catálogo</h1>
            <p class="text-sm text-[#678] mt-1">Escolha um filme e alugue por dia.</p>
        </div>

        <!-- Filtros -->
        <form method="GET" action="{{ route('movies.index') }}"
            class="grid grid-cols-1 sm:grid-cols-[1fr_180px_120px_auto] gap-4 items-end bg-[#1b2229] border border-[#2c3440] rounded-[3px] p-4 mb-10">
            <div>
                <label for="title" class="{{ $labelClass }}">Título</label>
                <input type="text" name="title" id="title" value="{{ request('title') }}"
                    placeholder="Buscar por título" class="{{ $fieldClass }}">
            </div>

            <div>
                <label for="genre_id" class="{{ $labelClass }}">Gênero</label>
                <select name="genre_id" id="genre_id" class="{{ $fieldClass }}">
                    <option value="">Todos</option>
                    @foreach ($genres as $genre)
                        <option value="{{ $genre->id }}" @selected((string) request('genre_id') === (string) $genre->id)>
                            {{ $genre->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="year" class="{{ $labelClass }}">Ano</label>
                <select name="year" id="year" class="{{ $fieldClass }} tabular-nums">
                    <option value="">Todos</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="px-5 py-2 bg-[#53c7ff] hover:bg-[#8fdcff] text-[#14181c] text-xs font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors">
                    Filtrar
                </button>
                @if ($hasFilters)
                    <a href="{{ route('movies.index') }}"
                        class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#678] hover:text-white transition-colors">
                        Limpar
                    </a>
                @endif
            </div>
        </form>

        <!-- Título de seção com contagem -->
        <div class="flex items-baseline justify-between border-b border-[#456] pb-1 mb-6">
            <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#9ab]">
                {{ $hasFilters ? 'Resultados' : 'Todos os filmes' }}
            </h2>
            <span class="text-[11px] text-[#678] tabular-nums">
                {{ $movies->total() }} {{ Str::plural('filme', $movies->total()) }}
            </span>
        </div>

        @if ($movies->isEmpty())
            <div class="py-16 text-center text-sm text-[#678]">
                Nenhum filme encontrado com esses filtros.
                <a href="{{ route('movies.index') }}"
                    class="text-[#53c7ff] hover:text-[#8fdcff] underline underline-offset-2 ml-1">Limpar filtros</a>
            </div>
        @else
            <!-- Grade de pôsteres -->
            <ul class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-x-5 gap-y-8">
                @foreach ($movies as $movie)
                    @php
                        $available = $movie->stock > 0;
                        $rating = $movie->reviews_avg_rating;
                    @endphp
                    <li>
                        <a href="{{ route('movies.show', $movie) }}" class="group block focus:outline-none">
                            <!-- Pôster -->
                            <div
                                class="relative aspect-[2/3] rounded-[3px] overflow-hidden bg-[#2c3440] ring-1 ring-inset ring-white/15 group-hover:ring-2 group-hover:ring-[#53c7ff] group-focus-visible:ring-2 group-focus-visible:ring-[#53c7ff] transition-shadow">
                                @if ($movie->cover_url)
                                    <img src="{{ $movie->cover_url }}" alt="Capa de {{ $movie->title }}"
                                        loading="lazy" class="w-full h-full object-cover {{ $available ? '' : 'opacity-50 grayscale' }}">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-center text-sm text-[#678] px-4">
                                        {{ $movie->title }}
                                    </div>
                                @endif
                            </div>

                            <!-- Informações -->
                            <div class="mt-3 space-y-1">
                                <h3 class="text-white text-sm font-semibold leading-snug line-clamp-2 group-hover:text-[#53c7ff] transition-colors">
                                    {{ $movie->title }}
                                </h3>
                                <p class="text-xs text-[#678] tabular-nums">
                                    {{ $movie->year }} &middot; {{ $movie->genre?->name ?? 'Sem gênero' }}
                                </p>

                                <div class="flex items-center justify-between pt-1 text-xs">
                                    <span class="text-[#9ab] tabular-nums">
                                        R$ {{ number_format($movie->priceTier->daily_price, 2, ',', '.') }}<span class="text-[#678]">/dia</span>
                                    </span>
                                    @if ($rating !== null)
                                        <span class="text-[#53c7ff] tabular-nums" title="Média das avaliações">
                                            &#9733; {{ number_format($rating, 1, ',', '') }}
                                        </span>
                                    @else
                                        <span class="text-[#678]">Sem avaliações</span>
                                    @endif
                                </div>

                                <p class="text-[11px] flex items-center gap-1.5 {{ $available ? 'text-[#9ab]' : 'text-[#ff9b9b]' }}">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full {{ $available ? 'bg-[#53c7ff]' : 'bg-[#ff5b5b]' }}"></span>
                                    {{ $available ? 'Disponível' : 'Indisponível' }}
                                </p>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>

            @if ($movies->hasPages())
                <div class="mt-12">
                    {{ $movies->links() }}
                </div>
            @endif
        @endif

    </div>
@endsection
