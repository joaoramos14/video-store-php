@extends('layouts.app')

@section('title', 'Gêneros - 7 Rent')

@section('content')
    <div>

        <!-- Cabeçalho da página -->
        <div class="flex items-end justify-between gap-4 mb-8">
            <div>
                <h1 class="text-white text-3xl font-bold tracking-tight">Gêneros</h1>
                <p class="text-sm text-[#678] mt-1">Gêneros do catálogo sincronizados com o TMDB.</p>
            </div>
            <a href="{{ route('admin.genres.create') }}"
                class="shrink-0 px-4 py-2 bg-[#53c7ff] hover:bg-[#8fdcff] text-[#14181c] text-xs font-bold uppercase tracking-[0.1em] rounded-[3px] transition-colors">
                Novo gênero
            </a>
        </div>

        <!-- Título de seção -->
        <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#9ab] border-b border-[#456] pb-1 mb-0">
            Todos os gêneros
        </h2>

        <!-- Lista -->
        <div class="bg-[#1b2229] border-x border-b border-[#2c3440] rounded-b-[3px] overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#2c3440] text-[11px] uppercase tracking-[0.1em] text-[#678]">
                        <th class="py-3 px-4 font-semibold">TMDB</th>
                        <th class="py-3 px-4 font-semibold">Nome</th>
                        <th class="py-3 px-4 font-semibold text-center">Filmes</th>
                        <th class="py-3 px-4 font-semibold text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#2c3440] text-sm">
                    @forelse($genres as $genre)
                        <tr class="hover:bg-[#2c3440]/50 transition-colors">
                            <td class="py-3 px-4 text-[#678] tabular-nums">
                                #{{ $genre->tmdb_id ?? '—' }}
                            </td>
                            <td class="py-3 px-4 font-semibold text-white">
                                {{ $genre->name }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span
                                    class="inline-block min-w-[3.5rem] bg-[#2c3440] text-[#9ab] text-xs px-2.5 py-0.5 rounded-[3px] tabular-nums">
                                    {{ $genre->movies_count }} {{ Str::plural('filme', $genre->movies_count) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-4 text-[11px] font-semibold uppercase tracking-[0.1em]">
                                    <a href="{{ route('admin.genres.edit', $genre) }}"
                                        class="text-[#9ab] hover:text-[#53c7ff] transition-colors">
                                        Editar
                                    </a>

                                    <form action="{{ route('admin.genres.destroy', $genre) }}" method="POST"
                                        onsubmit="return confirm('Tem certeza que deseja excluir este gênero?');"
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
                            <td colspan="4" class="py-12 text-center text-[#678] text-sm">
                                Nenhum gênero cadastrado.
                                <a href="{{ route('admin.genres.create') }}"
                                    class="text-[#53c7ff] hover:text-[#8fdcff] underline underline-offset-2 ml-1">Cadastrar o primeiro gênero</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Paginação -->
            @if ($genres->hasPages())
                <div class="p-4 border-t border-[#2c3440]">
                    {{ $genres->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
