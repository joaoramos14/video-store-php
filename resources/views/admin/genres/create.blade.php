@extends('layouts.app')

@section('title', 'Novo Gênero')

@section('content')
<div class="max-w-2xl mx-auto">
    
    <div class="mb-6 flex items-center justify-between border-b border-neutral-800 pb-4">
        <h1 class="text-xl font-bold uppercase tracking-wider text-white">Cadastrar Gênero</h1>
        <a href="{{ route('admin.genres.index') }}" class="text-xs uppercase font-semibold text-neutral-400 hover:text-sky-400 transition-colors">
            Voltar para lista
        </a>
    </div>

    <div class="bg-neutral-900 border border-neutral-800 rounded-md p-6 shadow-xl">
        <form action="{{ route('admin.genres.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-xs uppercase font-bold tracking-wider text-neutral-300 mb-2">
                    Nome do Gênero <span class="text-sky-400">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full bg-neutral-950 border border-neutral-800 rounded px-3 py-2 text-sm text-neutral-100 placeholder-neutral-600 focus:outline-none focus:border-sky-400 focus:ring-1 focus:ring-sky-400 transition-all">
                @error('name')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-xs uppercase font-bold tracking-wider text-neutral-300 mb-2">
                    Descrição
                </label>
                <textarea name="description" id="description" rows="4"
                    class="w-full bg-neutral-950 border border-neutral-800 rounded px-3 py-2 text-sm text-neutral-100 placeholder-neutral-600 focus:outline-none focus:border-sky-400 focus:ring-1 focus:ring-sky-400 transition-all">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end space-x-4 pt-4 border-t border-neutral-800">
                <a href="{{ route('admin.genres.index') }}" 
                   class="px-4 py-2 bg-neutral-800 hover:bg-neutral-700 text-neutral-300 text-xs font-bold uppercase tracking-wider rounded transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-5 py-2 bg-sky-500 hover:bg-sky-400 text-neutral-950 text-xs font-bold uppercase tracking-wider rounded transition-colors">
                    Salvar Gênero
                </button>
            </div>
        </form>
    </div>

</div>
@endsection