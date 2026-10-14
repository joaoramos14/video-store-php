@extends('layouts.app')

@section('title', 'Novo filme - 7 Rent')

@section('content')
    <div class="max-w-3xl mx-auto">

        <div class="mb-8">
            <a href="{{ route('admin.movies.index') }}"
                class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#678] hover:text-[#53c7ff] transition-colors">
                &larr; Filmes
            </a>
            <h1 class="text-white text-3xl font-bold tracking-tight mt-2">Novo filme</h1>
            <p class="text-sm text-[#678] mt-1">Busque no TMDB para preencher os dados ou digite manualmente.</p>
        </div>

        <!-- Busca no TMDB -->
        <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#9ab] border-b border-[#456] pb-1">
            Buscar no TMDB
        </h2>
        <div class="bg-[#1b2229] border-x border-b border-[#2c3440] rounded-b-[3px] p-6 mb-8 relative">
            <label for="tmdb_search" class="sr-only">Buscar filme no TMDB</label>
            <input type="text" id="tmdb_search" autocomplete="off" placeholder="Digite o nome do filme"
                class="w-full bg-[#2c3440] border border-transparent rounded-[3px] px-3 py-2.5 text-sm text-white placeholder-[#678] focus:outline-none focus:border-[#53c7ff] focus:bg-[#14181c] transition-colors">
            <p id="tmdb_status" class="hidden text-xs mt-2 text-[#678]" role="status"></p>

            <div id="tmdb_results"
                class="hidden absolute left-6 right-6 mt-1 bg-[#1b2229] border border-[#456] rounded-[3px] shadow-2xl max-h-96 overflow-y-auto z-50 divide-y divide-[#2c3440]">
            </div>
        </div>

        <!-- Formulário -->
        <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#9ab] border-b border-[#456] pb-1">
            Dados do filme
        </h2>
        <div class="bg-[#1b2229] border-x border-b border-[#2c3440] rounded-b-[3px] p-6">
            <form action="{{ route('admin.movies.store') }}" method="POST" class="space-y-6">
                @csrf

                @include('admin.movies._form', ['movie' => null, 'genres' => $genres])

                <div class="flex items-center justify-end gap-3 pt-5 border-t border-[#2c3440]">
                    <a href="{{ route('admin.movies.index') }}"
                        class="px-4 py-2 bg-[#456] hover:bg-[#567] text-white text-xs font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-5 py-2 bg-[#53c7ff] hover:bg-[#8fdcff] text-[#14181c] text-xs font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors">
                        Salvar filme
                    </button>
                </div>
            </form>
        </div>

    </div>

    <script>
        const tmdbSearchUrl = @json(route('admin.tmdb.search'));
        const tmdbShowUrl = @json(route('admin.tmdb.show', ['id' => '__ID__']));
        const searchBox = document.getElementById('tmdb_search');
        const resultsBox = document.getElementById('tmdb_results');
        const statusLine = document.getElementById('tmdb_status');
        let searchTimer = null;
        let currentResults = [];

        const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));

        function setStatus(text, isError = false) {
            statusLine.textContent = text;
            statusLine.classList.toggle('hidden', !text);
            statusLine.classList.toggle('text-[#ff9b9b]', isError);
            statusLine.classList.toggle('text-[#678]', !isError);
        }

        searchBox.addEventListener('input', () => {
            clearTimeout(searchTimer);
            const q = searchBox.value.trim();
            setStatus('');

            if (q.length < 2) {
                resultsBox.classList.add('hidden');
                return;
            }

            // Espera o usuário parar de digitar antes de buscar
            searchTimer = setTimeout(() => runSearch(q), 350);
        });

        async function runSearch(q) {
            try {
                const res = await fetch(`${tmdbSearchUrl}?query=${encodeURIComponent(q)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error();
                currentResults = await res.json();
                renderResults();
            } catch (e) {
                resultsBox.classList.add('hidden');
                setStatus('Não foi possível buscar no TMDB. Tente novamente.', true);
            }
        }

        function renderResults() {
            if (currentResults.length === 0) {
                resultsBox.innerHTML = `<div class="px-4 py-3 text-xs text-[#678]">Nenhum filme encontrado com esse nome.</div>`;
            } else {
                resultsBox.innerHTML = currentResults.map((m, i) => {
                    const poster = m.poster
                        ? `<img src="${escapeHtml(m.poster)}" alt="" class="w-9 aspect-[2/3] object-cover rounded-[3px] ring-1 ring-inset ring-white/15">`
                        : `<div class="w-9 aspect-[2/3] rounded-[3px] bg-[#2c3440]"></div>`;
                    return `
                        <button type="button" data-index="${i}"
                            class="w-full text-left px-4 py-3 flex items-center gap-4 hover:bg-[#2c3440] transition-colors">
                            ${poster}
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-white truncate">${escapeHtml(m.title)}</span>
                                <span class="block text-xs text-[#678] tabular-nums">${m.year || 'Ano não informado'}</span>
                            </span>
                        </button>`;
                }).join('');
            }
            resultsBox.classList.remove('hidden');
        }

        resultsBox.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-index]');
            if (!btn) return;
            loadDetails(currentResults[Number(btn.dataset.index)]);
        });

        // A busca não traz diretor nem duração: buscamos os detalhes do filme escolhido
        async function loadDetails(item) {
            resultsBox.classList.add('hidden');
            setStatus('Carregando dados do filme...');

            try {
                const res = await fetch(tmdbShowUrl.replace('__ID__', item.id), {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error();
                fillForm(await res.json());
                searchBox.value = item.title;
                setStatus('Dados preenchidos. Revise e informe a diária e o estoque.');
            } catch (e) {
                setStatus('Não foi possível carregar os dados desse filme.', true);
            }
        }

        function fillForm(m) {
            const set = (id, value) => { document.getElementById(id).value = value ?? ''; };

            set('f_title', m.title);
            set('f_director', m.director);
            set('f_synopsis', m.synopsis);
            set('f_year', m.year);
            set('f_duration', m.duration);
            set('f_cover', m.cover);
            set('f_tmdb_id', m.tmdb_id);

            // Escolhe o gênero local que tem o mesmo TMDB ID de algum gênero do filme
            const genreSelect = document.getElementById('f_genre_id');
            const ids = (m.genre_tmdb_ids ?? []).map(Number);
            const match = [...genreSelect.options].find(o => o.dataset.tmdbId && ids.includes(Number(o.dataset.tmdbId)));
            genreSelect.value = match ? match.value : '';

            document.getElementById('f_cover').dispatchEvent(new Event('input'));
        }

        document.addEventListener('click', (e) => {
            if (!searchBox.contains(e.target) && !resultsBox.contains(e.target)) {
                resultsBox.classList.add('hidden');
            }
        });
    </script>
@endsection
