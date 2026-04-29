<div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white">Código de Vestimenta</h2>
        <button onclick="document.getElementById('modal-add-dresscode').showModal()" class="btn btn-primary text-sm">
            + Agregar Código
        </button>
    </div>

    {{-- List --}}
    @if($event->dressCodes->isEmpty())
        <div class="text-center py-12 text-slate-500">
            <p>No has definido ningún código de vestimenta aún.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($event->dressCodes as $dc)
                <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700/50 flex justify-between items-start">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-medium text-white">{{ $dc->title }}</h3>
                            @if(!$dc->is_enabled)
                                <span class="px-2 py-0.5 rounded text-[10px] bg-red-500/10 text-red-400 border border-red-500/20">Oculto</span>
                            @endif
                        </div>
                        @if($dc->description)
                            <p class="text-sm text-slate-400 mt-1">{{ $dc->description }}</p>
                        @endif
                        @if($dc->examples)
                            <p class="text-xs text-slate-500 mt-2 italic">Ejemplos: {{ Str::limit($dc->examples, 60) }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick='openEditDressCode(@json($dc))' class="p-2 text-slate-400 hover:text-indigo-400 transition-colors" title="Editar">
                            ✏️
                        </button>
                        <form action="{{ route('client.dress-codes.destroy', $dc->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este código de vestimenta?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition-colors" title="Eliminar">
                                🗑️
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Add Modal --}}
<dialog id="modal-add-dresscode" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-lg">
        <h3 class="font-bold text-lg mb-4">Agregar Código de Vestimenta</h3>
        <form action="{{ route('client.eventos.dress-codes.store', $event->id) }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="label text-sm text-slate-400">Título <span class="text-red-400">*</span></label>
                    <input type="text" name="title" required class="input w-full" placeholder="Ej. Formal, Playa, Etiqueta...">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Descripción</label>
                    <textarea name="description" rows="2" class="input w-full" placeholder="Breve descripción..."></textarea>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Ejemplos (Opcional)</label>
                    <textarea name="examples" rows="2" class="input w-full" placeholder="Ej. Traje oscuro para hombres..."></textarea>
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
                <button type="button" onclick="document.getElementById('modal-add-dresscode').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- Edit Modal (Shared Structure, populated via JS) --}}
<dialog id="modal-edit-dresscode" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-lg">
        <h3 class="font-bold text-lg mb-4">Editar Código de Vestimenta</h3>
        <form id="form-edit-dresscode" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="label text-sm text-slate-400">Título <span class="text-red-400">*</span></label>
                    <input type="text" name="title" id="edit_dc_title" required class="input w-full">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Descripción</label>
                    <textarea name="description" id="edit_dc_description" rows="2" class="input w-full"></textarea>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Ejemplos (Opcional)</label>
                    <textarea name="examples" id="edit_dc_examples" rows="2" class="input w-full"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-sm text-slate-400">Orden</label>
                        <input type="number" name="display_order" id="edit_dc_order" class="input w-full">
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" id="edit_dc_enabled" value="1" class="checkbox checkbox-primary">
                            <span class="ml-2 text-sm text-slate-300">Visible</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" onclick="document.getElementById('modal-edit-dresscode').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    function openEditDressCode(data) {
        document.getElementById('edit_dc_title').value = data.title;
        document.getElementById('edit_dc_description').value = data.description || '';
        document.getElementById('edit_dc_examples').value = data.examples || '';
        document.getElementById('edit_dc_order').value = data.display_order;
        document.getElementById('edit_dc_enabled').checked = !!data.is_enabled;
        
        // Construct action URL for shallow resource: /panel/eventos.dress-codes/{id} -> NO, shallow is /panel/dress-codes/{id}
        // Actually resource name is 'client.eventos.dress-codes' but shallow makes it 'client.dress-codes.update' ? 
        // Need to check route name carefully.
        // Route::resource('eventos.dress-codes', ...) -> shallow()
        // Names: index=>events.dress-codes.index (excluded), store=>events.dress-codes.store
        // Update => dress-codes.update
        // Let's verify route names in next step. Assuming standard Laravel shallow naming.
        
        let url = "{{ route('client.dress-codes.update', ':id') }}";
        url = url.replace(':id', data.id);
        document.getElementById('form-edit-dresscode').action = url;
        
        document.getElementById('modal-edit-dresscode').showModal();
    }
</script>
