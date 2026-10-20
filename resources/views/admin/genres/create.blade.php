@extends('layouts.app')

@section('title', 'Novo gênero - 7 Rent')

@section('content')
    <div class="max-w-xl mx-auto">

        <div class="mb-8">
            <a href="{{ route('admin.genres.index') }}"
                class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#678] hover:text-[#53c7ff] transition-colors">
                &larr; Gêneros
            </a>
            <h1 class="text-white text-3xl font-bold tracking-tight mt-2">Novo gênero</h1>
            <p class="text-sm text-[#678] mt-1">Busque pelo nome para conectar o gênero ao TMDB.</p>
        </div>

        <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#9ab] border-b border-[#456] pb-1">
            Dados do gênero
        </h2>

        <div class="bg-[#1b2229] border-x border-b border-[#2c3440] rounded-b-[3px] p-6">
            <form action="{{ route('admin.genres.store') }}" method="POST" class="space-y-6" autocomplete="off">
                @csrf

                <!-- ID do TMDB (oculto) -->
                <input type="hidden" name="tmdb_id" id="tmdb_id" value="{{ old('tmdb_id') }}">

                <div class="relative">
                    <label for="genre_search"
                        class="block text-[11px] font-semibold uppercase tracking-[0.1em] text-[#9ab] mb-2">
                        Nome do gênero
                    </label>

                    <div class="relative">
                        <input type="text" name="name" id="genre_search" value="{{ old('name') }}" required
                            placeholder="Digite o nome, como Ação, Terror ou Comédia"
                            class="w-full bg-[#2c3440] border border-transparent rounded-[3px] px-3 py-2.5 pr-24 text-sm text-white placeholder-[#678] focus:outline-none focus:border-[#53c7ff] focus:bg-[#14181c] transition-colors">

                        <!-- Indicador de vínculo com o TMDB -->
                        <span id="badge-linked"
                            class="hidden absolute right-2 top-1/2 -translate-y-1/2 text-[10px] font-semibold uppercase tracking-[0.1em] bg-[#53c7ff]/15 text-[#53c7ff] border border-[#53c7ff]/40 px-2 py-0.5 rounded-[3px]">
                            Vinculado
                        </span>
                    </div>

                    <!-- Sugestões -->
                    <div id="suggestions-list"
                        class="hidden absolute left-0 right-0 mt-1 bg-[#1b2229] border border-[#456] rounded-[3px] shadow-2xl max-h-60 overflow-y-auto z-50 divide-y divide-[#2c3440]">
                    </div>

                    @error('name')
                        <p class="text-[#ff7b7b] text-xs mt-2">{{ $message }}</p>
                    @enderror
                    @error('tmdb_id')
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
                        Salvar gênero
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        // Gêneros do TMDB enviados pelo controller
        const tmdbGenres = @json($tmdbGenres);
        // IDs já cadastrados, convertidos para número para comparar com segurança
        const importedTmdbIds = @json($importedTmdbIds).map(id => Number(id));

        const searchInput = document.getElementById('genre_search');
        const tmdbIdInput = document.getElementById('tmdb_id');
        const suggestionsList = document.getElementById('suggestions-list');
        const badgeLinked = document.getElementById('badge-linked');

        // Os nomes vêm do TMDB: nunca entram no HTML sem passar por aqui
        const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));

        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();

            // Limpa o vínculo ao digitar
            tmdbIdInput.value = '';
            badgeLinked.classList.add('hidden');

            if (query.length === 0) {
                suggestionsList.classList.add('hidden');
                return;
            }

            const matches = tmdbGenres.filter(g => g.name.toLowerCase().includes(query));

            if (matches.length === 0) {
                suggestionsList.innerHTML = `
                <div class="px-4 py-3 text-xs text-[#678]">
                    Nenhum gênero encontrado no TMDB com esse nome.
                </div>
            `;
            } else {
                suggestionsList.innerHTML = matches.map(g => {
                    const isImported = importedTmdbIds.includes(Number(g.id));

                    if (isImported) {
                        return `
                        <div class="px-4 py-2.5 text-sm text-[#678] flex items-center justify-between cursor-not-allowed">
                            <span>${escapeHtml(g.name)}</span>
                            <span class="text-[10px] uppercase font-semibold tracking-[0.1em]">Já cadastrado</span>
                        </div>
                    `;
                    } else {
                        return `
                        <button type="button" data-id="${Number(g.id)}" data-name="${escapeHtml(g.name)}"
                            class="w-full text-left px-4 py-2.5 text-sm text-white hover:bg-[#2c3440] hover:text-[#53c7ff] transition-colors flex items-center justify-between">
                            <span class="font-medium">${escapeHtml(g.name)}</span>
                            <span class="text-[11px] text-[#678] tabular-nums">TMDB #${Number(g.id)}</span>
                        </button>
                    `;
                    }
                }).join('');
            }

            suggestionsList.classList.remove('hidden');
        });

        // Um único listener na lista, em vez de onclick dentro do HTML gerado
        suggestionsList.addEventListener('click', function(e) {
            const button = e.target.closest('button[data-id]');
            if (!button) return;
            selectGenre(Number(button.dataset.id), button.dataset.name);
        });

        function selectGenre(id, name) {
            searchInput.value = name;
            tmdbIdInput.value = id;

            suggestionsList.classList.add('hidden');
            badgeLinked.classList.remove('hidden');
        }

        // Fecha a lista ao clicar fora
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !suggestionsList.contains(e.target)) {
                suggestionsList.classList.add('hidden');
            }
        });
    </script>
@endsection
