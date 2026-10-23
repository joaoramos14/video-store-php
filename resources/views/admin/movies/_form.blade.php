{{-- Campos compartilhados entre criar e editar. $movie é opcional. --}}
@php
    $movie = $movie ?? null;
    $inputClass =
        'w-full bg-[#2c3440] border border-transparent rounded-[3px] px-3 py-2.5 text-sm text-white placeholder-[#678] focus:outline-none focus:border-[#53c7ff] focus:bg-[#14181c] transition-colors';
    $labelClass = 'block text-[11px] font-semibold uppercase tracking-[0.1em] text-[#9ab] mb-2';
    $errorClass = 'text-[#ff7b7b] text-xs mt-2';
@endphp

<input type="hidden" name="tmdb_id" id="f_tmdb_id" value="{{ old('tmdb_id', $movie?->tmdb_id) }}">

<div class="grid grid-cols-1 sm:grid-cols-[160px_1fr] gap-6">

    <!-- Capa -->
    <div>
        <div
            class="aspect-[2/3] w-40 rounded-[3px] ring-1 ring-inset ring-white/15 bg-[#2c3440] overflow-hidden flex items-center justify-center text-center text-xs text-[#678] px-3">
            <img id="cover_preview" alt="Prévia da capa" class="hidden w-full h-full object-cover">
            <span id="cover_empty">Sem capa</span>
        </div>
    </div>

    <div class="space-y-5">
        <div>
            <label for="f_title" class="{{ $labelClass }}">Título</label>
            <input type="text" name="title" id="f_title" required value="{{ old('title', $movie?->title) }}"
                class="{{ $inputClass }}">
            @error('title')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="f_director" class="{{ $labelClass }}">Diretor</label>
            <input type="text" name="director" id="f_director" value="{{ old('director', $movie?->director) }}"
                class="{{ $inputClass }}">
            @error('director')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div>
                <label for="f_year" class="{{ $labelClass }}">Ano</label>
                <input type="number" name="year" id="f_year" required min="1888" max="{{ date('Y') }}"
                    step="1" value="{{ old('year', $movie?->year) }}" class="{{ $inputClass }} tabular-nums">
                @error('year')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="f_duration" class="{{ $labelClass }}">Duração (min)</label>
                <input type="number" name="duration" id="f_duration" required min="1" step="1"
                    value="{{ old('duration', $movie?->duration) }}" class="{{ $inputClass }} tabular-nums">
                @error('duration')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="f_genre_id" class="{{ $labelClass }}">Gênero</label>
                <select name="genre_id" id="f_genre_id" required class="{{ $inputClass }}">
                    <option value="">Selecione</option>
                    @foreach ($genres as $genre)
                        <option value="{{ $genre->id }}" data-tmdb-id="{{ $genre->tmdb_id }}"
                            @selected((string) old('genre_id', $movie?->genre_id) === (string) $genre->id)>
                            {{ $genre->name }}
                        </option>
                    @endforeach
                </select>

                <div id="genre_missing_notice" class="hidden mt-3 rounded-[3px] border border-[#456] bg-[#14181c] p-3">
                    <p id="genre_missing_message" class="text-xs text-[#9ab]"></p>
                    <button type="button" id="genre_add_button"
                        class="mt-3 rounded-[3px] bg-[#53c7ff] px-3 py-2 text-xs font-bold uppercase tracking-[0.1em] text-[#14181c] transition-colors hover:bg-[#8fdcff]">
                        Adicionar e usar
                    </button>
                </div>

                @error('genre_id')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label for="f_daily_price" class="{{ $labelClass }}">Diária (R$)</label>
                <input type="number" name="daily_price" id="f_daily_price" required step="0.01" min="0"
                    value="{{ old('daily_price', $movie?->daily_price) }}" placeholder="0,00"
                    class="{{ $inputClass }} tabular-nums">
                @error('daily_price')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="f_stock" class="{{ $labelClass }}">Estoque</label>
                <input type="number" name="stock" id="f_stock" required step="1" min="0"
                    value="{{ old('stock', $movie?->stock ?? 1) }}" class="{{ $inputClass }} tabular-nums">
                @error('stock')
                    <p class="{{ $errorClass }}">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="f_synopsis" class="{{ $labelClass }}">Sinopse</label>
            <textarea name="synopsis" id="f_synopsis" rows="5" class="{{ $inputClass }} resize-y leading-relaxed">{{ old('synopsis', $movie?->synopsis) }}</textarea>
            @error('synopsis')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="f_cover" class="{{ $labelClass }}">Capa</label>
            <input type="text" name="cover" id="f_cover" value="{{ old('cover', $movie?->cover) }}"
                placeholder="https://..." class="{{ $inputClass }}">
            <p class="text-xs text-[#678] mt-2">Link da imagem. A busca no TMDB preenche este campo.</p>
            @error('cover')
                <p class="{{ $errorClass }}">{{ $message }}</p>
            @enderror
        </div>

        @if ($movie?->tmdb_id)
            <p class="text-xs text-[#678]">Vinculado ao TMDB <span
                    class="text-[#53c7ff] tabular-nums">#{{ $movie->tmdb_id }}</span></p>
        @endif
        @error('tmdb_id')
            <p class="{{ $errorClass }}">{{ $message }}</p>
        @enderror
    </div>
</div>

<script>
    // Prévia da capa (mesma regra do Movie::coverUrl: só URL completa)
    const coverInput = document.getElementById('f_cover');
    const coverPreview = document.getElementById('cover_preview');
    const coverEmpty = document.getElementById('cover_empty');

    function coverSrc(value) {
        return value.startsWith('http') ? value : null;
    }

    function refreshCover() {
        const src = coverSrc(coverInput.value.trim());
        coverPreview.classList.toggle('hidden', !src);
        coverEmpty.classList.toggle('hidden', !!src);
        if (src) coverPreview.src = src;
    }

    coverInput.addEventListener('input', refreshCover);
    refreshCover();
</script>
