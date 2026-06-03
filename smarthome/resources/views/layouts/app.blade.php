<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SmartHome - {{ $title ?? 'Dashboard' }}</title>
    {{-- tailwindcss cdn --}}
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('styles')
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen">
        {{-- top bar on mobile only  --}}
        {{-- container with user picture, username, notification and settins icons --}}
        <nav class=" bg-gray-800/50 backdrop-blur-lg border-b border-gray-700 lg:hidden">
            <div class="flex justify-between items-center px-4 py-3">
                <div class="flex items-center justify-center gap-2">
                    <a href="#" class="text-gray-300 hover:text-white transition">
                        <i class="bi bi-person-circle"></i>
                    </a>
                    <h1 class="text-lg font-bold">{{auth()->check() ? auth()->user()->prenom. ' ' .auth()->user()->nom : 'SmartHome'}}</h1>
                </div>
                <div class="flex items-center space-x-2">
                    @if(auth()->check())
                        {{-- <a href="#" class="text-gray-300 hover:text-white transition">
                            <i class="bi bi-bell-fill"></i>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-white transition">
                            <i class="bi bi-gear-fill"></i>
                        </a> --}}

                        <form method="POST" action="{{ route('logout') }}" class="text-white hover:text-white transition bg-blue-500 rounded-lg p-2">
                            @csrf
                            <button type="submit">
                                <i class="bi bi-box-arrow-left"></i>
                            </button>
                        </form>
                    @else
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition">
                        <i class="bi bi-box-arrow-in-right"></i>
                    </a>
                    @endif
                </div>
            </div>
        </nav>

        <!-- Sidebar -->
        <aside class="hidden fixed pt-2 left-0 top-0 h-full w-64 bg-gray-800/50 backdrop-blur-lg border-r border-gray-700 lg:block">
            <div class="p-6">
                <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-500 to-purple-600 bg-clip-text">
                    {{ auth()->check() ? auth()->user()->prenom .' '. auth()->user()->nom : "Smarthome" }}
                </h1>
            </div>
            <nav class="mt-8">
                <a href="{{ route('dashboard') }}" class="w-full flex items-center px-6 py-3 {{ request()->routeIs('dashboard') ? "text-blue-500" : "text-gray-300" }} hover:bg-gray-700/50 transition">
                    <i class="bi bi-house-fill"></i>
                    Tableau de bord
                </a>
                <a href="{{ route('rooms') }}" class="w-full flex items-center px-6 py-3 {{ request()->routeIs('rooms.*') ? "text-blue-500" : "text-gray-300" }} hover:bg-gray-700/50 transition px-3 py-2 rounded">
                    <i class="bi bi-plus-circle-fill"></i>
                    Pièces
                </a>
                <a href="{{ route('devices') }}" class="w-full flex items-center px-6 py-3 {{ request()->routeIs('devices.*') ? "text-blue-500" : "text-gray-300" }} hover:bg-gray-700/50 transition px-3 py-2 rounded">
                    <i class="bi bi-webcam-fill"></i>
                    Appareils
                </a>
        </nav>
        </aside>


        <!-- Main Content -->
        <main class="lg:ml-64 lg:p-8 p-4 md:pt-8">
            @yield('content')
        </main>

        {{-- Barre de navigation en bas sur mobile uniquement --}}
        <nav class="fixed bottom-0 left-0 w-full bg-gray-800/50 backdrop-blur-lg border-t border-gray-700 lg:hidden z-50">
            <div class="flex justify-around items-center h-16">
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center {{ request()->routeIs('dashboard') ? "text-blue-500" : "text-gray-300" }} hover:bg-gray-700/50 transition px-3 py-2 rounded">
                    <i class="bi bi-house-fill"></i>
                    Acceuil
                </a>
                <a href="{{ route('rooms') }}" class="flex flex-col items-center {{ request()->routeIs('rooms.*') ? "text-blue-500" : "text-gray-300" }} hover:bg-gray-700/50 transition px-3 py-2 rounded">
                    <i class="bi bi-plus-circle-fill"></i>
                    Pièces
                </a>
                <a href="{{ route('devices') }}" class="flex flex-col items-center {{ request()->routeIs('devices.*') ? "text-blue-500" : "text-gray-300" }} hover:bg-gray-700/50 transition px-3 py-2 rounded">
                    <i class="bi bi-webcam-fill"></i>
                    Appareils
                </a>
                {{-- <a href="{{ route('devices') }}" class="flex flex-col items-center {{ request()->routeIs('devices') ? "text-blue-500" : "text-gray-300" }} hover:bg-gray-700/50 transition px-3 py-2 rounded">
                    <i class="bi bi-lightbulb-fill"></i>
                    Appareils
                </a>
                <a href="{{ route('scenes') }}" class="flex flex-col items-center {{ request()->routeIs('scenes') ? "text-blue-500" : "text-gray-300" }} hover:bg-gray-700/50 transition px-3 py-2 rounded">
                    <i class="bi bi-diagram-3-fill"></i>
                    Scènes
                </a> --}}
            </div>
        </nav>
    </div>
    @stack('scripts')
</body>
</html>
