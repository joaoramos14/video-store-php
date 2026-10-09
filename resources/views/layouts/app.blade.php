<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-neutral-950">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Locadora')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-neutral-950 text-neutral-200 flex flex-col min-h-screen font-sans antialiased selection:bg-sky-500 selection:text-neutral-950">

    <!-- Header Navigation -->
    <header class="bg-neutral-900/90 border-b border-neutral-800 backdrop-blur sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">

            <div class="flex items-center space-x-8">
                <a href="{{ route('movies.index') }}"
                    class="text-xl font-black tracking-wider text-white uppercase flex items-center space-x-2">
                    <span class="text-sky-400 font-bold">7</span><span class="text-neutral-100">RENT</span>
                </a>

                <nav class="hidden md:flex space-x-6 text-sm font-semibold tracking-wide uppercase text-neutral-400">
                    <a href="{{ route('movies.index') }}"
                        class="hover:text-sky-400 transition-colors duration-150">Catálogo</a>
                    <a href="{{ route('rentals.index') }}"
                        class="hover:text-sky-400 transition-colors duration-150">Meus Aluguéis</a>
                </nav>
            </div>

            @if (auth()->user()?->isAdmin())
                <div
                    class="flex items-center space-x-6 border-l border-neutral-800 pl-6 text-xs font-bold tracking-widest uppercase text-neutral-400">
                    <a href="{{ route('admin.genres.index') }}"
                        class="hover:text-sky-400 transition-colors duration-150">Gêneros</a>
                    <a href="{{ route('admin.movies.index') }}"
                        class="hover:text-sky-400 transition-colors duration-150">Filmes</a>
                    <a href="{{ route('admin.rentals.index') }}"
                        class="hover:text-sky-400 transition-colors duration-150">Devoluções</a>
                </div>
            @endif

        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full">
        @if (session('status'))
            <div class="mb-6 p-4 bg-sky-950/60 border border-sky-500/40 text-sky-200 rounded-md text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 p-4 bg-red-950/60 border border-red-500/40 text-red-200 rounded-md text-sm">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-neutral-900 border-t border-neutral-800 text-neutral-500 text-xs py-8 mt-auto">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <p>Locadora de Filmes &copy; {{ date('Y') }} &mdash; Todos os direitos reservados.</p>
            <p class="text-neutral-600">
                Dados fornecidos via API do TMDB.
            </p>
        </div>
    </footer>

</body>

</html>
