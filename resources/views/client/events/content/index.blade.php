@extends('client.layout')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'dress-code' }">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Contenido del Evento</h1>
            <p class="text-slate-400 mt-1">Gestiona la información pública de tu evento</p>
        </div>
        <div>
            <a href="{{ route('client.events.index') }}" class="btn btn-secondary">
                ← Volver a Eventos
            </a>
        </div>
    </div>

    <!-- Stats / Info Bar (Optional, maybe link to public page) -->
    <div class="bg-slate-800 rounded-xl p-4 border border-slate-700 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
            </div>
            <div>
                <p class="text-sm text-slate-400">Ver cómo luce en tu web</p>
                <a href="{{ url('/eventos/' . $event->slug) }}" target="_blank" class="text-indigo-400 font-medium hover:text-indigo-300">
                    {{ url('/eventos/' . $event->slug) }} ↗
                </a>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="border-b border-slate-700">
        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
            <button 
                @click="activeTab = 'dress-code'" 
                :class="activeTab === 'dress-code' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
            >
                👔 Código de Vestimenta
            </button>

            <button 
                @click="activeTab = 'phrases'" 
                :class="activeTab === 'phrases' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
            >
                ❤️ Frases
            </button>

            <button 
                @click="activeTab = 'story'" 
                :class="activeTab === 'story' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
            >
                📖 Historia
            </button>

            <button 
                @click="activeTab = 'schedule'" 
                :class="activeTab === 'schedule' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
            >
                🕒 Itinerario
            </button>

            <button 
                @click="activeTab = 'locations'" 
                :class="activeTab === 'locations' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
            >
                📍 Ubicaciones
            </button>

            <button 
                @click="activeTab = 'gifts'" 
                :class="activeTab === 'gifts' ? 'border-indigo-500 text-indigo-400' : 'border-transparent text-slate-400 hover:text-slate-300 hover:border-slate-300'"
                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
            >
                🎁 Regalos
            </button>
        </nav>
    </div>

    <!-- Tab Contents -->
    <div class="pt-4">
        <div x-show="activeTab === 'dress-code'" x-cloak>
            @include('client.events.content.partials.dress-code')
        </div>

        <div x-show="activeTab === 'phrases'" x-cloak>
            @include('client.events.content.partials.phrases')
        </div>

        <div x-show="activeTab === 'story'" x-cloak>
            @include('client.events.content.partials.story')
        </div>

        <div x-show="activeTab === 'schedule'" x-cloak>
            @include('client.events.content.partials.schedule')
        </div>

        <div x-show="activeTab === 'locations'" x-cloak>
            @include('client.events.content.partials.locations')
        </div>

        <div x-show="activeTab === 'gifts'" x-cloak>
            @include('client.events.content.partials.gifts')
        </div>
    </div>
</div>

<!-- Reusable Modal Component Logic could go here or in layout -->
@endsection
