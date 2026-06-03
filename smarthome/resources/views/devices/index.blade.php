@extends('layouts.app')
@section('content')
    <div class="container mx-auto py-8">
        <h1 class="text-3xl font-bold mb-6">Gestion des Appareils</h1>
        {{-- <!-- Content for the rooms index page -->
        <div class="bg-gray-800/50 backdrop-blur-lg border border-gray-700 rounded-lg p-6 mb-6">
            <p class="text-gray-300">Ici, vous pouvez voir toutes les pièces de votre maison connectée, ajouter de nouvelles pièces, et gérer les pièces existantes.</p>
            <a href="{{ route('rooms.create') }}" class="mt-4 inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
                Ajouter une nouvelle pièce
            </a>
        </div> --}}
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <p class="underline">Toutes les appareils</p>
                {{-- <p class="underline drop-shadow-[0_0_10px_rgba(255,255,255)]">Scroll -></p> --}}
            </div>
            <div class="flex flex-col flex-shrink md:flex-row lg:flex-row xl:flex-row overflow-x-auto mt-2 gap-6">
                @if(auth()->check())
                    @forelse($devices as $device)
                        <div class="bg-gray-700/50 backdrop-blur-lg border border-gray-600 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold">
                                    @if($device->type === 'temperature')
                                        <i class="bi bi-thermometer text-red-400"></i>
                                    @elseif($device->type === 'light')
                                        <i class="bi bi-lightbulb-fill text-yellow-400"></i>
                                    @elseif($device->type === 'shutter')
                                        <i class="bi bi-window-sash text-blue-400"></i>
                                    @elseif($device->type === 'sensor')
                                        <i class="bi bi-thermometer text-red-400"></i>
                                    @endif
                                    {{ $device->nom }}
                                </h3>
                                <p class="text-gray-300">{{ $device->piece->maison->nom }}</p>
                            </div>
                            <p class="text-gray-300">Piece: {{ $device->piece->nom }}</p>
                            <p class="text-gray-300">Type: {{ $device->type }}</p>
                            <p class="text-gray-300">mqtt: {{ $device->mqttTopic }}</p>
                            {{-- Additional device details can go here --}}
                            <p>
                                @if($device->type === 'temperature')
                                    <p class="text-gray-300">État: {{ $device->etat === 'on' ? 'Allumé' : 'Éteint' }}</p>
                                @elseif($device->type === 'light')
                                    <p class="text-gray-300">État: {{ $device->etat === 'on' ? 'Allumé' : 'Éteint' }}</p>
                                @elseif($device->type === 'shutter')
                                    <p class="text-gray-300">État: {{ $device->etat === 'open' ? 'Ouvert' : 'Fermé' }}</p>
                                @elseif($device->type === 'sensor')
                                    <p class="text-gray-300">Valeur: {{ $device->valeur }}</p>
                                @endif
                            </p>
                            {{-- ajouter un bouton pour allumer/up ou eteindre/down l'appareil selon son type et son état actuel --}}
                            @if($device->type === 'temperature')
                                <form action="{{ route('commande.send') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="idDevice" value="{{ $device->id }}">
                                    <input type="hidden" name="type" value="{{ $device->type }}">
                                    <input type="hidden" name="valeur" value="{{ $device->etat === 'on' ? 'off' : 'on' }}" class="w-full px-3 py-2 bg-gray-600 text-white rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="submit" class="mt-2 w-full
                                    {{ $device->etat === 'on' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}
                                    text-white font-semibold py-2 px-4 rounded transition">
                                        {{ $device->etat === 'on' ? 'Eteindre' : 'Allumer' }}
                                    </button>
                                </form>
                            @elseif($device->type === 'light')
                                <form action="{{ route('commande.send') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="idDevice" value="{{ $device->id }}">
                                    <input type="hidden" name="type" value="{{ $device->type }}">
                                    <input type="hidden" name="valeur" value="{{ $device->etat === 'on' ? 'off' : 'on' }}" class="w-full px-3 py-2 bg-gray-600 text-white rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <button type="submit" class="mt-2 w-full
                                    {{ $device->etat === 'on' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}
                                    text-white font-semibold py-2 px-4 rounded transition">
                                        {{ $device->etat === 'on' ? 'Éteindre' : 'Allumer' }}
                                    </button>
                                </form>
                            @elseif($device->type === 'shutter')
                                <form action="{{ route('commande.send') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="idDevice" value="{{ $device->id }}">
                                    <input type="hidden" name="type" value="{{ $device->type }}">
                                    <input type="hidden" name="valeur" value="{{ $device->etat === 'open' ? 'close' : 'open' }}">
                                    <button type="submit" class="mt-2 w-full
                                    {{ $device->etat === 'open' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}
                                    text-white font-semibold py-2 px-4 rounded transition">
                                        {{ $device->etat === 'open' ? 'Fermer' : 'Ouvrir' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-300 mt-4">Aucune appareil trouvé. </p>
                    @endforelse
                @endif
            </div>
        </div>

    </div>
@endsection
