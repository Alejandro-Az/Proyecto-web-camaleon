@extends('client.layout')

@section('title', 'Crear evento')

@section('content')
    <section class="max-w-2xl mx-auto bg-[var(--surface-alt)] rounded-3xl p-6 md:p-8 shadow">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold">Crear nuevo evento</h2>
            <p class="text-sm text-[var(--ink-soft)] mt-1">Configura lo básico. Luego podrás editar módulos, contenido y diseño.</p>
        </div>

        <form method="POST" action="{{ route('client.events.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm text-[var(--ink-soft)] mb-2" for="name">Nombre del evento</label>
                <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] id="name" name="name" type="text" value="{{ old('name') }}" required class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full" placeholder="Ej. Boda Ana y Luis">
                @error('name')<p class="text-red-300 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-[var(--ink-soft)] mb-2" for="type">Tipo</label>
                    <select id="type" name="type" required class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full">
                        <option value="wedding" @selected(old('type') === 'wedding')>Boda</option>
                        <option value="xv" @selected(old('type') === 'xv')>XV Años</option>
                        <option value="birthday" @selected(old('type') === 'birthday')>Cumpleaños</option>
                        <option value="baby_shower" @selected(old('type') === 'baby_shower')>Baby Shower</option>
                        <option value="other" @selected(old('type') === 'other')>Otro</option>
                    </select>
                    @error('type')<p class="text-red-300 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm text-[var(--ink-soft)] mb-2" for="plan_key">Plan</label>
                    <select id="plan_key" name="plan_key" required class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full">
                        <option value="standard" @selected(old('plan_key') === 'standard')>Standard</option>
                        <option value="premium" @selected(old('plan_key', 'premium') === 'premium')>Premium</option>
                    </select>
                    @error('plan_key')<p class="text-red-300 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-[var(--ink-soft)] mb-2" for="event_date">Fecha</label>
                    <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] id="event_date" name="event_date" type="date" value="{{ old('event_date') }}" required class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full">
                    @error('event_date')<p class="text-red-300 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm text-[var(--ink-soft)] mb-2" for="start_time">Hora de inicio</label>
                    <cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] id="start_time" name="start_time" type="time" value="{{ old('start_time', '18:00') }}" class="cml-input border border-[var(--rule)] rounded-xl bg-[var(--surface)] focus:ring-2 focus:ring-[var(--accent)] w-full">
                    @error('start_time')<p class="text-red-300 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('client.events.index') }}" class="btn btn-ghost">Cancelar</a>
                <button type="submit" class="btn inline-flex items-center justify-center px-4 py-2 rounded-full bg-[var(--accent)] text-white hover:brightness-110 text-sm font-semibold transition-all">Crear evento</button>
            </div>
        </form>
    </section>
@endsection
