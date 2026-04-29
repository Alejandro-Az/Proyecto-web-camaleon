<div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white">Nuestra Historia / Sobre Nosotros</h2>
        <button onclick="document.getElementById('modal-add-story').showModal()" class="btn btn-primary text-sm">
            + Agregar Sección
        </button>
    </div>

    @if($event->stories->isEmpty())
        <div class="text-center py-12 text-slate-500">
            <p>Aún no has escrito ninguna historia.</p>
        </div>
    @else
        <div class="space-y-6">
            @foreach($event->stories as $story)
                <div class="bg-slate-900/50 p-6 rounded-lg border border-slate-700/50">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-bold text-white">{{ $story->title ?? 'Sin Título' }}</h3>
                            @if(!$story->is_enabled)
                                <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] bg-red-500/10 text-red-400 border border-red-500/20">Oculto</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                           <button onclick='openEditStory(@json($story))' class="p-2 text-slate-400 hover:text-indigo-400 transition-colors" title="Editar">
                                ✏️
                            </button>
                            <form action="{{ route('client.stories.destroy', $story->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta sección?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-slate-400 hover:text-red-400 transition-colors" title="Eliminar">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="prose prose-invert prose-sm max-w-none text-slate-300 whitespace-pre-line">
                        {{ $story->body }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Add Modal --}}
<dialog id="modal-add-story" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-2xl">
        <h3 class="font-bold text-lg mb-4">Agregar Sección de Historia</h3>
        <form action="{{ route('client.eventos.stories.store', $event->id) }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="label text-sm text-slate-400">Título (Opcional)</label>
                    <input type="text" name="title" class="input w-full" placeholder="Ej. Cómo nos conocimos">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Contenido <span class="text-red-400">*</span></label>
                    <textarea name="body" required rows="8" class="input w-full" placeholder="Escribe tu historia aquí..."></textarea>
                    <p class="text-xs text-slate-500 mt-1">Puedes organizar tu texto en párrafos.</p>
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
                <button type="button" onclick="document.getElementById('modal-add-story').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- Edit Modal --}}
<dialog id="modal-edit-story" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-2xl">
        <h3 class="font-bold text-lg mb-4">Editar Sección de Historia</h3>
        <form id="form-edit-story" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="label text-sm text-slate-400">Título (Opcional)</label>
                    <input type="text" name="title" id="edit_st_title" class="input w-full">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Contenido <span class="text-red-400">*</span></label>
                    <textarea name="body" id="edit_st_body" required rows="8" class="input w-full"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-sm text-slate-400">Orden</label>
                        <input type="number" name="display_order" id="edit_st_order" class="input w-full">
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" id="edit_st_enabled" value="1" class="checkbox checkbox-primary">
                            <span class="ml-2 text-sm text-slate-300">Visible</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" onclick="document.getElementById('modal-edit-story').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    function openEditStory(data) {
        document.getElementById('edit_st_title').value = data.title || '';
        document.getElementById('edit_st_body').value = data.body;
        document.getElementById('edit_st_order').value = data.display_order;
        document.getElementById('edit_st_enabled').checked = !!data.is_enabled;
        
        let url = "{{ route('client.stories.update', ':id') }}";
        url = url.replace(':id', data.id);
        document.getElementById('form-edit-story').action = url;
        
        document.getElementById('modal-edit-story').showModal();
    }
</script>
