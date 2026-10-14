@extends('layouts.app')

@section('title', 'Editar filme - 7 Rent')

@section('content')
    <div class="max-w-3xl mx-auto">

        <div class="mb-8">
            <a href="{{ route('admin.movies.index') }}"
                class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#678] hover:text-[#53c7ff] transition-colors">
                &larr; Filmes
            </a>
            <h1 class="text-white text-3xl font-bold tracking-tight mt-2">Editar filme</h1>
            <p class="text-sm text-[#678] mt-1">{{ $movie->title }}</p>
        </div>

        <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#9ab] border-b border-[#456] pb-1">
            Dados do filme
        </h2>

        <div class="bg-[#1b2229] border-x border-b border-[#2c3440] rounded-b-[3px] p-6">
            <form action="{{ route('admin.movies.update', $movie) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                @include('admin.movies._form', ['movie' => $movie, 'genres' => $genres])

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-[#2c3440]">
                    <a href="{{ route('admin.movies.index') }}"
                        class="px-4 py-2 bg-[#456] hover:bg-[#567] text-white text-xs font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2 bg-[#53c7ff] hover:bg-[#8fdcff] text-[#14181c] text-xs font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors">
                        Salvar alterações
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection
