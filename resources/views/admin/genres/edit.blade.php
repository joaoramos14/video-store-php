@extends('layouts.app')

@section('title', 'Editar gênero - 7 Rent')

@section('content')
    <div class="max-w-xl mx-auto">

        <div class="mb-8">
            <a href="{{ route('admin.genres.index') }}"
                class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#678] hover:text-[#53c7ff] transition-colors">
                &larr; Gêneros
            </a>
            <h1 class="text-white text-3xl font-bold tracking-tight mt-2">Editar gênero</h1>
            <p class="text-sm text-[#678] mt-1">Atualize o nome que aparece no catálogo.</p>
        </div>

        <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#9ab] border-b border-[#456] pb-1">
            Dados do gênero
        </h2>

        <div class="bg-[#1b2229] border-x border-b border-[#2c3440] rounded-b-[3px] p-6">
            <form action="{{ route('admin.genres.update', $genre) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- ID do TMDB (somente leitura) -->
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.1em] text-[#9ab] mb-2">
                        ID no TMDB
                    </label>
                    <div
                        class="w-full bg-[#14181c] border border-[#2c3440] rounded-[3px] px-3 py-2.5 text-sm text-[#53c7ff] tabular-nums select-none">
                        #{{ $genre->tmdb_id ?? 'N/A' }}
                    </div>
                    <p class="text-xs text-[#678] mt-2">Esse ID não muda. Ele mantém o gênero sincronizado com o TMDB.</p>
                </div>

                <!-- Nome -->
                <div>
                    <label for="name" class="block text-[11px] font-semibold uppercase tracking-[0.1em] text-[#9ab] mb-2">
                        Nome do gênero
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $genre->name) }}" required
                        class="w-full bg-[#2c3440] border border-transparent rounded-[3px] px-3 py-2.5 text-sm text-white placeholder-[#678] focus:outline-none focus:border-[#53c7ff] focus:bg-[#14181c] transition-colors">

                    @error('name')
                        <p class="text-[#ff7b7b] text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-[#2c3440]">
                    <a href="{{ route('admin.genres.index') }}"
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
