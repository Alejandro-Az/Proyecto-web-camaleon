<div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white">Ubicaciones</h2>
        <button onclick="document.getElementById('modal-add-location').showModal()" class="btn btn-primary text-sm">
            + Agregar Ubicación
        </button>
    </div>

    @if($event->locations->isEmpty())
        <div class="text-center py-12 text-slate-500">
            <p>No hay ubicaciones registradas.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($event->locations as $loc)
                <div class="bg-slate-900/50 rounded-lg border border-slate-700/50 overflow-hidden flex flex-col h-full">
                    {{-- Header --}}
                    <div class="p-4 bg-slate-800/50 border-b border-slate-700/50 flex justify-between items-center">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-indigo-400">
                                {{ $loc->type ?? 'Ubicación' }}
                            </span>
                            <h3 class="font-bold text-white text-lg leading-tight">{{ $loc->name }}</h3>
                        </div>
                        <div class="flex items-center gap-1">
                            @if(!$loc->is_enabled)
                                <span class="mr-2 px-2 py-0.5 rounded text-[10px] bg-red-500/10 text-red-400 border border-red-500/20">Oculto</span>
                            @endif
                            <button onclick='openEditLocation(@json($loc))' class="p-1.5 text-slate-400 hover:text-indigo-400 transition-colors" title="Editar">
                                ✏️
                            </button>
                            <form action="{{ route('client.locations.destroy', $loc->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta ubicación?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-400 transition-colors" title="Eliminar">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="p-4 flex-1">
                        @if($loc->address)
                            <div class="flex items-start gap-2 text-slate-400 mb-3">
                                <span>📍</span>
                                <p class="text-sm">{{ $loc->address }}</p>
                            </div>
                        @endif
                        
                        @if($loc->maps_url)
                            <a href="{{ $loc->maps_url }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300">
                                Ver en Google Maps ↗
                            </a>
                        @else
                            <span class="text-xs text-slate-600 italic">Sin enlace de mapa</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Add Modal --}}
<dialog id="modal-add-location" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-lg">
        <h3 class="font-bold text-lg mb-4">Agregar Ubicación</h3>
        <form action="{{ route('client.eventos.locations.store', $event->id) }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="label text-sm text-slate-400">Tipo (Ej. Ceremonia, Recepción)</label>
                    <select name="type" class="select w-full bg-slate-900 border-slate-700">
                        <option value="Ceremonia">Ceremonia</option>
                        <option value="Recepción">Recepción</option>
                        <option value="Hospedaje">Hospedaje</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Nombre del Lugar <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required class="input w-full" placeholder="Ej. Hacienda San Pedro">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Dirección</label>
                    <textarea name="address" rows="2" class="input w-full" placeholder="Calle, Número, Ciudad..."></textarea>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">URL Google Maps</label>
                    <input type="url" name="maps_url" class="input w-full" placeholder="https://maps.google.com/...">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-sm text-slate-400">Orden</label>
                        <input type="number" name="display_order" value="0" class="input w-full">
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="is_enabled" value="1" checked class="checkbox checkbox-primary">
                            <span class="ml-2 text-sm text-slate-300">Visible</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" onclick="document.getElementById('modal-add-location').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- Edit Modal --}}
<dialog id="modal-edit-location" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-lg">
        <h3 class="font-bold text-lg mb-4">Editar Ubicación</h3>
        <form id="form-edit-location" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="label text-sm text-slate-400">Tipo</label>
                    <select name="type" id="edit_loc_type" class="select w-full bg-slate-900 border-slate-700">
                        <option value="Ceremonia">Ceremonia</option>
                        <option value="Recepción">Recepción</option>
                        <option value="Hospedaje">Hospedaje</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Nombre del Lugar <span class="text-red-400">*</span></label>
                    <input type="text" name="name" id="edit_loc_name" required class="input w-full">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Dirección</label>
                    <textarea name="address" id="edit_loc_address" rows="2" class="input w-full"></textarea>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">URL Google Maps</label>
                    <input type="url" name="maps_url" id="edit_loc_maps" class="input w-full">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-sm text-slate-400">Orden</label>
                        <input type="number" name="display_order" id="edit_loc_order" class="input w-full">
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" id="edit_loc_enabled" value="1" class="checkbox checkbox-primary">
                            <span class="ml-2 text-sm text-slate-300">Visible</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" onclick="document.getElementById('modal-edit-location').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    function openEditLocation(data) {
        // Handle select value manual fallback if type is not in options?
        // Simple assignment usually works if value matches
        document.getElementById('edit_loc_type').value = data.type || 'Otro';
        document.getElementById('edit_loc_name').value = data.name;
        document.getElementById('edit_loc_address').value = data.address || '';
        document.getElementById('edit_loc_maps').value = data.maps_url || '';
        document.getElementById('edit_loc_order').value = data.display_order;
        document.getElementById('edit_loc_enabled').checked = !!data.is_enabled;
        
        let url = "{{ route('client.locations.update', ':id') }}";
        url = url.replace(':id', data.id);
        document.getElementById('form-edit-location').action = url;
        
        document.getElementById('modal-edit-location').showModal();
    }
</script>
