<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-[#14181c]">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Locadora')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-[#14181c] text-[#9ab] flex flex-col min-h-screen font-sans text-[15px] leading-relaxed antialiased selection:bg-[#53c7ff] selection:text-[#14181c]">

    <!-- Header -->
    <header class="bg-[#14181c] border-b border-[#2c3440]">
        <div class="max-w-[950px] mx-auto px-4 h-[72px] flex items-center justify-between">

            <a href="{{ route('movies.index') }}" class="flex items-center gap-3 group">
                <!-- Três pontos: assinatura visual da marca -->
                <span class="flex items-center -space-x-1" aria-hidden="true">
                    <span class="w-[18px] h-[18px] rounded-full bg-[#1f7fb8]"></span>
                    <span class="w-[18px] h-[18px] rounded-full bg-[#53c7ff]"></span>
                    <span class="w-[18px] h-[18px] rounded-full bg-[#b5e8ff]"></span>
                </span>
                <span class="text-white text-[22px] font-bold tracking-tight">7 Rent</span>
            </a>

            <nav class="flex items-center gap-5 text-[12px] font-semibold uppercase tracking-[0.1em]">
                <a href="{{ route('movies.index') }}"
                    class="text-[#9ab] hover:text-white transition-colors">Catálogo</a>
                <a href="{{ route('rentals.index') }}" class="text-[#9ab] hover:text-white transition-colors">Meus
                    aluguéis</a>
            </nav>
        </div>

        @if (auth()->user()?->isAdmin())
            <!-- Subnavegação do administrador -->
            <div class="bg-[#1b2229] border-t border-[#2c3440]">
                <div
                    class="max-w-[950px] mx-auto px-4 h-10 flex items-center gap-5 text-[11px] font-semibold uppercase tracking-[0.1em]">
                    <span class="text-[#678]">Admin</span>
                    <a href="{{ route('admin.genres.index') }}"
                        class="text-[#9ab] hover:text-[#53c7ff] transition-colors">Gêneros</a>
                    <a href="{{ route('admin.movies.index') }}"
                        class="text-[#9ab] hover:text-[#53c7ff] transition-colors">Filmes</a>
                    <a href="{{ route('admin.rentals.index') }}"
                        class="text-[#9ab] hover:text-[#53c7ff] transition-colors">Aluguéis</a>
                </div>
            </div>
        @endif
    </header>

    <!-- Conteúdo -->
    <main class="max-w-[950px] mx-auto px-4 py-10 flex-grow w-full">
        @if (session('status'))
            <div class="mb-6 px-4 py-3 bg-[#53c7ff]/10 border border-[#53c7ff]/40 text-[#b5e8ff] rounded-[3px] text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 px-4 py-3 bg-[#ff5b5b]/10 border border-[#ff5b5b]/40 text-[#ffb3b3] rounded-[3px] text-sm">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Rodapé -->
    <footer class="border-t border-[#2c3440] text-[#678] text-xs py-8 mt-auto">
        <div class="max-w-[950px] mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <p>&copy; {{ date('Y') }} 7 Rent. Todos os direitos reservados.</p>
            <div class="flex items-center gap-3">
                <a href="https://www.themoviedb.org/" target="_blank" rel="noopener">
                    <img src="{{ asset('images/tmdb-logo.svg') }}" alt="TMDB" class="h-6">
                </a>
                <p class="max-w-xs">
                    This product uses the TMDB API but is not endorsed or certified by TMDB.
                </p>
            </div>
        </div>
    </footer>

</body>

</html>
