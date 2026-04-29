<div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white">Itinerario / Timeline</h2>
        <button onclick="document.getElementById('modal-add-schedule').showModal()" class="btn btn-primary text-sm">
            + Agregar Actividad
        </button>
    </div>

    @if($event->schedules->isEmpty())
        <div class="text-center py-12 text-slate-500">
            <p>Todavía no agregas actividades al itinerario.</p>
        </div>
    @else
        <div class="relative pl-4 border-l border-slate-700 space-y-8">
            @foreach($event->schedules as $item)
                <div class="relative pl-6">
                    {{-- Timeline dot --}}
                    <div class="absolute -left-[5px] top-1 w-2.5 h-2.5 rounded-full bg-indigo-500 ring-4 ring-slate-800"></div>
                    
                    <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700/50 flex justify-between items-start group">
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="font-mono text-indigo-400 font-bold">
                                    {{ \Carbon\Carbon::parse($item->starts_at)->format('H:i') }}
                                </span>
                                @if($item->ends_at)
                                    <span class="text-slate-500 text-sm">- {{ \Carbon\Carbon::parse($item->ends_at)->format('H:i') }}</span>
                                @endif
                                <h3 class="text-white font-medium ml-2">{{ $item->title }}</h3>
                                @if(!$item->is_enabled)
                                    <span class="px-2 py-0.5 rounded text-[10px] bg-red-500/10 text-red-400 border border-red-500/20">Oculto</span>
                                @endif
                            </div>

                            @if($item->location_label)
                                <div class="flex items-center gap-1 text-sm text-slate-400 mb-2">
                                    <span>📍 {{ $item->location_label }}</span>
                                </div>
                            @endif

                            @if($item->description)
                                <p class="text-sm text-slate-400">{{ $item->description }}</p>
                            @endif
                        </div>
                        
                        <div class="flex items-center gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                            <button onclick='openEditSchedule(@json($item))' class="p-2 text-slate-400 hover:text-indigo-400 transition-colors" title="Editar">
                                ✏️
                            </button>
                            <form action="{{ route('client.schedules.destroy', $item->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta actividad?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition-colors" title="Eliminar">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Add Modal --}}
<dialog id="modal-add-schedule" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-lg">
        <h3 class="font-bold text-lg mb-4">Agregar Actividad</h3>
        <form action="{{ route('client.eventos.schedules.store', $event->id) }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-sm text-slate-400">Hora Inicio <span class="text-red-400">*</span></label>
                        {{-- Using datetime-local or just time if logic allows. Controller validation: date_format:Y-m-d H:i:s
                             Ideally we should pre-fill the date part with event date if we use datetime-local.
                             Or use type="time" and handle date merging in backend.
                             BUT: validation expects full datetime. Let's start with datetime-local for simplicity.
                        --}}
                        <input type="datetime-local" name="starts_at" required class="input w-full text-sm">
                    </div>
                    <div>
                        <label class="label text-sm text-slate-400">Hora Fin (Opcional)</label>
                        <input type="datetime-local" name="ends_at" class="input w-full text-sm">
                    </div>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Título <span class="text-red-400">*</span></label>
                    <input type="text" name="title" required class="input w-full" placeholder="Ej. Ceremonia, Banquete...">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Ubicación (Nombre corto)</label>
                    <input type="text" name="location_label" class="input w-full" placeholder="Ej. Capilla Principal">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Descripción</label>
                    <textarea name="description" rows="2" class="input w-full"></textarea>
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
                <button type="button" onclick="document.getElementById('modal-add-schedule').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- Edit Modal --}}
<dialog id="modal-edit-schedule" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-lg">
        <h3 class="font-bold text-lg mb-4">Editar Actividad</h3>
        <form id="form-edit-schedule" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-sm text-slate-400">Hora Inicio <span class="text-red-400">*</span></label>
                        <input type="datetime-local" name="starts_at" id="edit_sch_starts" required class="input w-full text-sm">
                    </div>
                    <div>
                        <label class="label text-sm text-slate-400">Hora Fin (Opcional)</label>
                        <input type="datetime-local" name="ends_at" id="edit_sch_ends" class="input w-full text-sm">
                    </div>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Título <span class="text-red-400">*</span></label>
                    <input type="text" name="title" id="edit_sch_title" required class="input w-full">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Ubicación (Nombre corto)</label>
                    <input type="text" name="location_label" id="edit_sch_loc" class="input w-full">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Descripción</label>
                    <textarea name="description" id="edit_sch_desc" rows="2" class="input w-full"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-sm text-slate-400">Orden</label>
                        <input type="number" name="display_order" id="edit_sch_order" class="input w-full">
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" id="edit_sch_enabled" value="1" class="checkbox checkbox-primary">
                            <span class="ml-2 text-sm text-slate-300">Visible</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" onclick="document.getElementById('modal-edit-schedule').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    function openEditSchedule(data) {
        // Format dates for input type="datetime-local" (YYYY-MM-DDTHH:mm)
        // Data likely comes as YYYY-MM-DD HH:mm:ss string
        const formatDateTime = (str) => str ? str.replace(' ', 'T').substring(0, 16) : '';

        document.getElementById('edit_sch_starts').value = formatDateTime(data.starts_at);
        document.getElementById('edit_sch_ends').value = formatDateTime(data.ends_at);
        document.getElementById('edit_sch_title').value = data.title;
        document.getElementById('edit_sch_loc').value = data.location_label || '';
        document.getElementById('edit_sch_desc').value = data.description || '';
        document.getElementById('edit_sch_order').value = data.display_order;
        document.getElementById('edit_sch_enabled').checked = !!data.is_enabled;
        
        let url = "{{ route('client.schedules.update', ':id') }}";
        url = url.replace(':id', data.id);
        document.getElementById('form-edit-schedule').action = url;
        
        document.getElementById('modal-edit-schedule').showModal();
    }
</script>
