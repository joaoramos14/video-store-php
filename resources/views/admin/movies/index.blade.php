@extends('layouts.app')

@section('title', 'Filmes - 7 Rent')

@php
@endphp

@section('content')
    <div>

        <div class="flex items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-white text-3xl font-bold tracking-tight">Filmes</h1>
                <p class="text-sm text-[#678] mt-1">Filmes disponíveis para aluguel no catálogo.</p>
            </div>
            <a href="{{ route('admin.movies.create') }}"
                class="shrink-0 px-4 py-2 bg-[#53c7ff] hover:bg-[#8fdcff] text-[#14181c] text-xs font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors">
                Novo filme
            </a>
        </div>

        <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#9ab] border-b border-[#456] pb-1">
            Todos os filmes
        </h2>

        <div class="bg-[#1b2229] border-x border-b border-[#2c3440] rounded-b-[3px] overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#2c3440] text-[11px] uppercase tracking-[0.1em] text-[#678]">
                        <th class="py-3 px-4 font-semibold">Filme</th>
                        <th class="py-3 px-4 font-semibold">Gênero</th>
                        <th class="py-3 px-4 font-semibold text-right">Diária</th>
                        <th class="py-3 px-4 font-semibold text-center">Estoque</th>
                        <th class="py-3 px-4 font-semibold text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2c3440] text-sm">
                    @forelse($movies as $movie)
                        <tr class="hover:bg-[#2c3440]/50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-4">
                                    @if ($movie->cover_url)
                                        <img src="{{ $movie->cover_url }}" alt="Capa de {{ $movie->title }}"
                                            loading="lazy"
                                            class="w-10 aspect-[2/3] object-cover rounded-[3px] ring-1 ring-inset ring-white/15 bg-[#2c3440]">
                                    @else
                                        <div class="w-10 aspect-[2/3] rounded-[3px] ring-1 ring-inset ring-white/10 bg-[#2c3440]"></div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-semibold text-white truncate">{{ $movie->title }}</p>
                                        <p class="text-xs text-[#678] tabular-nums">
                                            {{ $movie->year }}@if ($movie->director) &middot; {{ $movie->director }}@endif
                                            &middot; {{ $movie->duration }} min
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-[#9ab]">{{ $movie->genre?->name ?? '—' }}</td>
                            <td class="py-3 px-4 text-right text-[#9ab] tabular-nums whitespace-nowrap">
                                R$ {{ number_format($movie->daily_price, 2, ',', '.') }}</td>
                            <td class="py-3 px-4 text-center">
                                <span
                                    class="inline-block min-w-[2.5rem] text-xs px-2.5 py-0.5 rounded-[3px] tabular-nums {{ $movie->stock > 0 ? 'bg-[#2c3440] text-[#9ab]' : 'bg-[#ff5b5b]/15 text-[#ff9b9b]' }}">
                                    {{ $movie->stock }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-4 text-[11px] font-semibold uppercase tracking-[0.1em]">
                                    <a href="{{ route('admin.movies.edit', $movie) }}"
                                        class="text-[#9ab] hover:text-[#53c7ff] transition-colors">Editar</a>
                                    <form action="{{ route('admin.movies.destroy', $movie) }}" method="POST"
                                        onsubmit="return confirm('Tem certeza que deseja excluir este filme?');"
                                        class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="text-[#678] hover:text-[#ff7b7b] transition-colors uppercase tracking-[0.1em] font-semibold">
                                            Excluir
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-[#678] text-sm">
                                Nenhum filme cadastrado.
                                <a href="{{ route('admin.movies.create') }}"
                                    class="text-[#53c7ff] hover:text-[#8fdcff] underline underline-offset-2 ml-1">Cadastrar o primeiro filme</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($movies->hasPages())
                <div class="p-4 border-t border-[#2c3440]">
                    {{ $movies->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
