@extends('client.layout')

@section('title', 'Invitados - ' . $event->name)

@section('content')
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('client.events.show', $event) }}" class="inline-flex items-center gap-2 text-slate-400 hover:text-white transition-colors mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Volver al evento
            </a>
            <h1 class="text-2xl font-bold text-white">Gestión de Invitados</h1>
            <p class="text-slate-400 text-sm">Administra la lista y el estado de tus invitados.</p>
        </div>
         <div class="flex gap-3">
             <button onclick="openAddModal()" class="btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Invitado
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-4 text-center">
            <div class="text-3xl font-bold text-white">{{ $stats['total'] }}</div>
            <div class="text-xs text-slate-400 uppercase tracking-wider">Total Invitaciones</div>
        </div>
        <div class="card p-4 text-center bg-emerald-500/10 border-emerald-500/20">
            <div class="text-3xl font-bold text-emerald-400">{{ $stats['confirmed_guests'] }}</div>
            <div class="text-xs text-emerald-200/70 uppercase tracking-wider">Personas Confirmadas</div>
        </div>
        <div class="card p-4 text-center">
             <div class="text-3xl font-bold text-yellow-400">{{ $stats['pending'] }}</div>
             <div class="text-xs text-yellow-200/70 uppercase tracking-wider">Pendientes</div>
        </div>
        <div class="card p-4 text-center">
             <div class="text-3xl font-bold text-red-400">{{ $stats['no'] }}</div>
             <div class="text-xs text-red-200/70 uppercase tracking-wider">Rechazados</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-6 flex gap-2 overflow-x-auto pb-2">
        <a href="{{ route('client.events.guests.index', $event) }}" class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}">Todos</a>
        <a href="{{ route('client.events.guests.index', ['event' => $event->id, 'status' => 'yes']) }}" class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ request('status') == 'yes' ? 'bg-emerald-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}">Confirmados</a>
        <a href="{{ route('client.events.guests.index', ['event' => $event->id, 'status' => 'pending']) }}" class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ request('status') == 'pending' ? 'bg-yellow-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}">Pendientes</a>
         <a href="{{ route('client.events.guests.index', ['event' => $event->id, 'status' => 'no']) }}" class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ request('status') == 'no' ? 'bg-red-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}">Rechazados</a>
    </div>

    {{-- Guests Table --}}
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-800/50 text-slate-200 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4">Invitado / Código</th>
                        <th class="px-6 py-4">Contacto</th>
                        <th class="px-6 py-4 text-center">Asientos</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700">
                    @forelse($guests as $guest)
                        <tr class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-white text-base flex items-center gap-2">
                                    {{ $guest->name }}
                                    @if($guest->rsvp_message)
                                        <span class="cursor-help text-indigo-400" title="Mensaje: {{ $guest->rsvp_message }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                        </span>
                                    @endif
                                    @if(is_array($guest->dietary_tags) && count($guest->dietary_tags) > 0)
                                        <div class="flex gap-0.5">
                                            @foreach($guest->dietary_tags as $tag)
                                                <span class="w-2 h-2 rounded-full bg-red-400/80" title="Dieta: {{ ucfirst(str_replace('_', ' ', $tag)) }}"></span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 mt-1">
                                    <code class="bg-slate-900 px-2 py-0.5 rounded text-xs font-mono text-indigo-300 border border-slate-700 select-all cursor-pointer" onclick="navigator.clipboard.writeText('{{ $guest->invitation_code }}'); alert('Código copiado!')">{{ $guest->invitation_code }}</code>
                                    <a href="{{ url('/eventos/' . $event->slug . '?i=' . $guest->invitation_code) }}" target="_blank" class="text-xs text-slate-500 hover:text-indigo-400" title="Abrir invitación">
                                        ↗ Link
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                @if($guest->email) <div class="flex items-center gap-1">✉️ {{ $guest->email }}</div> @endif
                                @if($guest->phone) <div class="flex items-center gap-1 mt-1">📞 {{ $guest->phone }}</div> @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block min-w-[2rem] text-center font-bold {{ $guest->rsvp_status == 'yes' ? 'text-emerald-400' : 'text-slate-500' }}">
                                    {{ $guest->guests_confirmed ?? '-' }}
                                </span>
                                <span class="text-slate-600">/</span>
                                <span class="inline-block min-w-[2rem] text-center font-medium text-slate-300">
                                    {{ $guest->invited_seats }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $badges = [
                                        'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                                        'yes'     => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'no'      => 'bg-red-500/10 text-red-400 border-red-500/20',
                                        'maybe'   => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                    ];
                                    $labels = [
                                        'pending' => 'Pendiente',
                                        'yes'     => 'Confirmado',
                                        'no'      => 'No Asistirá',
                                        'maybe'   => 'Tal vez',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium border {{ $badges[$guest->rsvp_status] ?? 'bg-slate-700 text-slate-300' }}">
                                    {{ $labels[$guest->rsvp_status] ?? $guest->rsvp_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        onclick='openEditModal(@json($guest))'
                                        class="p-2 text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors" 
                                        title="Editar"
                                    >
                                        ✏️
                                    </button>
                                    <form action="{{ route('client.guests.destroy', $guest->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este invitado?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-red-400 bg-slate-800 hover:bg-slate-700 rounded-lg transition-colors" title="Eliminar">
                                            🗑️
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <p>No hay invitados en esta lista.</p>
                                    @if(request('status'))
                                        <a href="{{ route('client.events.guests.index', $event) }}" class="text-indigo-400 hover:underline">Ver todos</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-700">
            {{ $guests->links() }}
        </div>
    </div>

    {{-- Add Guest Modal --}}
    <dialog id="addGuestModal" class="modal bg-slate-900/90 backdrop-blur fixed inset-0 w-full h-full z-50 flex items-center justify-center p-4 hidden">
        <div class="card w-full max-w-lg shadow-2xl relative animate-fade-in-up">
            <button type="button" onclick="closeAddModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white">✕</button>
            <h3 class="text-xl font-bold text-white mb-4">Agregar Nuevo Invitado</h3>
            
            <form action="{{ route('client.events.guests.store', $event) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Nombre Completo *</label>
                    <input type="text" name="name" required class="input w-full" placeholder="Ej. Familia Pérez">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Lugares (Asientos) *</label>
                        <input type="number" name="invited_seats" required min="1" value="1" class="input w-full">
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-slate-400 mb-1">Código (Opcional)</label>
                         <input type="text" name="invitation_code" class="input w-full" placeholder="Auto-generar">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Celular (Opcional)</label>
                    <input type="tel" name="phone" class="input w-full" placeholder="+52...">
                </div>
                 <div>
                    <label class="block text-sm font-medium text-slate-400 mb-1">Email (Opcional)</label>
                    <input type="email" name="email" class="input w-full" placeholder="correo@ejemplo.com">
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full justify-center">Guardar Invitado</button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- Edit Guest Modal (JS Populated) --}}
    <dialog id="editGuestModal" class="modal bg-slate-900/90 backdrop-blur fixed inset-0 w-full h-full z-50 flex items-center justify-center p-4 hidden">
        <div class="card w-full max-w-lg shadow-2xl relative animate-fade-in-up">
            <button type="button" onclick="closeEditModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white">✕</button>
            <h3 class="text-xl font-bold text-white mb-4">Editar Invitado</h3>
            
            <form id="editGuestForm" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                {{-- Tabs for Edit Mode --}}
                <div class="flex border-b border-slate-700 mb-4" x-data="{ tab: 'data' }"> <!-- Simple logic, doing vanilla JS below -->
                     {{-- Using simple layout instead of complex tabs for speed --}}
                </div>

                <div class="space-y-4">
                    <h4 class="text-indigo-400 font-medium text-sm border-b border-slate-700 pb-1">Datos Generales</h4>
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Nombre</label>
                        <input type="text" name="name" id="edit_name" required class="input w-full">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                             <label class="block text-sm font-medium text-slate-400 mb-1">Código</label>
                             <input type="text" name="invitation_code" id="edit_code" required class="input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Asientos Asignados</label>
                            <input type="number" name="invited_seats" id="edit_seats" required min="1" class="input w-full">
                        </div>
                    </div>
                     <div class="grid grid-cols-2 gap-4">
                        <div>
                             <label class="block text-sm font-medium text-slate-400 mb-1">Celular</label>
                             <input type="tel" name="phone" id="edit_phone" class="input w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Email</label>
                            <input type="email" name="email" id="edit_email" class="input w-full">
                        </div>
                    </div>

                    <h4 class="text-emerald-400 font-medium text-sm border-b border-slate-700 pb-1 pt-2">Estado RSVP</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Estado</label>
                            <select name="rsvp_status" id="edit_status" class="input w-full bg-slate-800">
                                <option value="pending">Pendiente</option>
                                <option value="yes">Confirmado</option>
                                <option value="maybe">Tal vez</option>
                                <option value="no">No Asistirá</option>
                            </select>
                        </div>
                         <div>
                            <label class="block text-sm font-medium text-slate-400 mb-1">Personas Confirmadas</label>
                            <input type="number" name="guests_confirmed" id="edit_confirmed" min="0" class="input w-full">
                            <p class="text-[10px] text-slate-500 mt-1">Dejar en blanco si es pendiente/no.</p>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-400 mb-1">Mensaje RSVP</label>
                        <textarea name="rsvp_message" id="edit_message" rows="2" class="input w-full" placeholder="Mensaje del invitado..."></textarea>
                    </div>

                    <div>
                         <label class="block text-sm font-medium text-slate-400 mb-1">Restricciones Alimenticias</label>
                         <div class="grid grid-cols-2 gap-2 mb-2">
                            @foreach(['vegano', 'vegetariano', 'sin_gluten', 'diabetico', 'sin_lactosa', 'alergia_nueces'] as $tag)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="dietary_tags[]" value="{{ $tag }}" class="toggle-checkbox dietary-checkbox">
                                    <span class="ml-2 text-sm text-slate-400 capitalize">{{ str_replace('_', ' ', $tag) }}</span>
                                </label>
                            @endforeach
                         </div>
                         <textarea name="dietary_notes" id="edit_dietary_notes" rows="2" class="input w-full" placeholder="Detalles adicionales de dieta..."></textarea>
                    </div>
                    
                    <div class="flex items-center gap-2 pt-2">
                        <input type="hidden" name="show_in_public_list" value="0">
                        <input type="checkbox" name="show_in_public_list" id="edit_public" value="1" class="toggle-checkbox">
                        <span class="text-sm text-slate-400">Mostrar en lista pública de asistencia</span>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-700">
                    <button type="submit" class="btn-primary w-full justify-center">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </dialog>

    <script>
        function openAddModal() {
            const modal = document.getElementById('addGuestModal');
            modal.classList.remove('hidden');
            modal.showModal();
        }

        function closeAddModal() {
            const modal = document.getElementById('addGuestModal');
            modal.close();
            modal.classList.add('hidden');
        }

        function openEditModal(guest) {
            const modal = document.getElementById('editGuestModal');
            const form = document.getElementById('editGuestForm');
            
            // Set action URL
            form.action = `/panel/invitados/${guest.id}`;
            
            // Populate fields
            document.getElementById('edit_name').value = guest.name;
            document.getElementById('edit_code').value = guest.invitation_code;
            document.getElementById('edit_seats').value = guest.invited_seats;
            document.getElementById('edit_phone').value = guest.phone || '';
            document.getElementById('edit_email').value = guest.email || '';
            document.getElementById('edit_status').value = guest.rsvp_status;
            document.getElementById('edit_confirmed').value = guest.guests_confirmed;
            document.getElementById('edit_public').checked = guest.show_in_public_list;
            
            document.getElementById('edit_message').value = guest.rsvp_message || '';
            document.getElementById('edit_dietary_notes').value = guest.dietary_notes || '';
            
            // Reset and populate tags
            document.querySelectorAll('.dietary-checkbox').forEach(cb => {
                cb.checked = (guest.dietary_tags || []).includes(cb.value);
            });

            modal.classList.remove('hidden');
            modal.showModal();
        }

        function closeEditModal() {
            const modal = document.getElementById('editGuestModal');
            modal.close();
            modal.classList.add('hidden');
        }
    </script>
@endsection
