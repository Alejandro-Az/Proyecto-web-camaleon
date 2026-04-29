<div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white">Frases Románticas</h2>
        <button onclick="document.getElementById('modal-add-phrase').showModal()" class="btn btn-primary text-sm">
            + Agregar Frase
        </button>
    </div>

    @if($event->romanticPhrases->isEmpty())
        <div class="text-center py-12 text-slate-500">
            <p>No hay frases registradas.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($event->romanticPhrases as $phrase)
                <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700/50 flex justify-between items-center">
                    <div class="flex-1 pr-4">
                        <blockquote class="text-white italic">"{{ $phrase->phrase }}"</blockquote>
                        @if($phrase->author)
                            <p class="text-sm text-slate-400 mt-1">— {{ $phrase->author }}</p>
                        @endif
                        @if(!$phrase->is_enabled)
                             <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] bg-red-500/10 text-red-400 border border-red-500/20">Oculto</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick='openEditPhrase(@json($phrase))' class="p-2 text-slate-400 hover:text-indigo-400 transition-colors" title="Editar">
                            ✏️
                        </button>
                        <form action="{{ route('client.phrases.destroy', $phrase->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta frase?');">
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
<dialog id="modal-add-phrase" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-lg">
        <h3 class="font-bold text-lg mb-4">Agregar Frase</h3>
        <form action="{{ route('client.eventos.phrases.store', $event->id) }}" method="POST">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="label text-sm text-slate-400">Frase <span class="text-red-400">*</span></label>
                    <textarea name="phrase" required rows="3" class="input w-full" placeholder="Escribe una frase romántica..."></textarea>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Autor (Opcional)</label>
                    <input type="text" name="author" class="input w-full" placeholder="Ej. Pablo Neruda">
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
                <button type="button" onclick="document.getElementById('modal-add-phrase').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

{{-- Edit Modal --}}
<dialog id="modal-edit-phrase" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-lg">
        <h3 class="font-bold text-lg mb-4">Editar Frase</h3>
        <form id="form-edit-phrase" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label class="label text-sm text-slate-400">Frase <span class="text-red-400">*</span></label>
                    <textarea name="phrase" id="edit_ph_phrase" required rows="3" class="input w-full"></textarea>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Autor (Opcional)</label>
                    <input type="text" name="author" id="edit_ph_author" class="input w-full">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-sm text-slate-400">Orden</label>
                        <input type="number" name="display_order" id="edit_ph_order" class="input w-full">
                    </div>
                    <div class="flex items-end pb-3">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" id="edit_ph_enabled" value="1" class="checkbox checkbox-primary">
                            <span class="ml-2 text-sm text-slate-300">Visible</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" onclick="document.getElementById('modal-edit-phrase').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<script>
    function openEditPhrase(data) {
        document.getElementById('edit_ph_phrase').value = data.phrase;
        document.getElementById('edit_ph_author').value = data.author || '';
        document.getElementById('edit_ph_order').value = data.display_order;
        document.getElementById('edit_ph_enabled').checked = !!data.is_enabled;
        
        let url = "{{ route('client.phrases.update', ':id') }}";
        url = url.replace(':id', data.id);
        document.getElementById('form-edit-phrase').action = url;
        
        document.getElementById('modal-edit-phrase').showModal();
    }
</script>
