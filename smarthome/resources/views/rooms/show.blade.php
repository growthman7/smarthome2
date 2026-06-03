@extends('layouts.app')
@section('content')
    @if(session('success'))
        <div class="alert bg-green-500 text-white p-2">
            {{ session('message') }}
        </div>
    @endif
    <div class="container mx-auto py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">{{ $piece->nom }}</h1>
            {{-- ajouter un bouton  pour modal d'ajout d'un appareil dans la pièce --}}
            <button data-modal-target="addDeviceModal" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition">
                <i class="bi bi-plus-circle"></i> Ajouter un appareil
            </button>
        </div>
        <!-- Content for the room details page -->
        <div class="bg-gray-800/50 backdrop-blur-lg border border-gray-700 rounded-lg p-6 mb-6">
            {{-- <h2 class="text-xl font-semibold mb-4">{{ $piece->nom }}</h2> --}}
            <p class="text-gray-300">Détails de la pièce et ses appareils connectés.</p>
            <!-- Example content for devices in the room -->
            <div class="mt-4">
                @foreach($piece->devices as $device)
                    <div class="bg-gray-700/50 backdrop-blur-lg border border-gray-600 rounded-lg p-4 mb-4">
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
                            {{ $device->nom }}</h3>
                        <p class="text-gray-300">Type: {{ $device->type }}</p>
                        <p>mqtt: {{ $device->mqttTopic }}</p>
                        {{-- Additional device details can go here --}}
                        <p>
                            @if($device->type === 'light')
                                <p class="text-gray-300">État: {{ $device->etat === 'on' ? 'Allumé' : 'Éteint' }}</p>
                            @elseif($device->type === 'shutter')
                                <p class="text-gray-300">État: {{ $device->etat === 'open' ? 'Ouvert' : 'Fermé' }}</p>
                            @elseif($device->type === 'sensor')
                                <p class="text-gray-300">Valeur: {{ $device->valeur }}</p>
                            @endif
                        </p>
                        {{-- ajouter un bouton pour allumer/up ou eteindre/down l'appareil selon son type et son état actuel --}}
                        @if($device->type === 'temperature')
                            @if($device->etat === 'on')
                                <form action="{{ route('commande.send') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="idDevice" value="{{ $device->id }}">
                                    <input type="hidden" name="type" value="{{ $device->type }}">
                                    <input type="hidden" name="valeur" value="off">
                                    <button type="submit" class="send-command bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded transition">
                                        Éteindre
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('commande.send') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="idDevice" value="{{ $device->id }}">
                                    <input type="hidden" name="type" value="{{ $device->type }}">
                                    <input type="hidden" name="valeur" value="on">
                                    <button type="submit" class="send-command bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition">
                                        Allumer
                                    </button>
                                </form>
                            @endif
                        @elseif($device->type === 'light')
                            @if($device->etat === 'on')
                                <form action="{{ route('commande.send') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="idDevice" value="{{ $device->id }}">
                                    <input type="hidden" name="type" value="{{ $device->type }}">
                                    <input type="hidden" name="valeur" value="off">
                                    <button type="submit" class="send-command bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded transition">
                                        Éteindre
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('commande.send') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="idDevice" value="{{ $device->id }}">
                                    <input type="hidden" name="type" value="{{ $device->type }}">
                                    <input type="hidden" name="valeur" value="on">
                                    <button type="submit" class="send-command bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition">
                                        Allumer
                                    </button>
                                </form>
                            @endif
                        @elseif($device->type === 'shutter')
                            @if($device->etat === 'open')
                                <form action="{{ route('commande.send') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="idDevice" value="{{ $device->id }}">
                                    <input type="hidden" name="type" value="{{ $device->type }}">
                                    <input type="hidden" name="valeur" value="close">
                                    <button type="submit" class="send-command bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded transition">
                                        Fermer
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('commande.send') }}" method="POST" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="idDevice" value="{{ $device->id }}">
                                    <input type="hidden" name="type" value="{{ $device->type }}">
                                    <input type="hidden" name="valeur" value="open">
                                    <button type="submit" class="send-command bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition">
                                        Ouvrir
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    {{-- Modal for adding a new device (hidden by default) --}}
    <div id="addDeviceModal" class="fixed inset-0 flex items-center justify-center dark:bg-black dark:bg-opacity-50 backdrop-blur-lg hidden" data-type="modal">
        <div class="block bg-gray-800/50 backdrop-blur-lg border border-gray-700 rounded-lg p-6 w-full max-w-md min-h-[200px]">
            <h2 class="text-xl font-semibold mb-4">Ajouter un nouvel appareil</h2>
            <form action="{{ route('devices.store') }}" method="POST">
                @csrf
                <input type="hidden" name="piece_id" value="{{ $piece->id }}">
                <div class="mb-4">
                    <label for="nom" class="block text-gray-300 mb-2">Nom de l'appareil</label>
                    <input type="text" name="nom" id="nom" class="w-full px-3 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label for="type" class="block text-gray-300 mb-2">Type d'appareil</label>
                    <select name="type" id="type" class="w-full px-3 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="temperature">Température</option>
                        <option value="light">Lumière</option>
                        <option value="shutter">Volet</option>
                        <option value="sensor">Capteur</option>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="document.getElementById('addDeviceModal').classList.add('hidden')" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded transition mr-2">
                        Annuler
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded transition">
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
<script src="{{ asset('js/app.js') }}"></script>
@endpush
