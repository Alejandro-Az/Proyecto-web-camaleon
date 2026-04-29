<div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-white">Mesa de Regalos</h2>
        <button onclick="document.getElementById('modal-add-gift').showModal()" class="btn btn-primary text-sm">
            + Agregar Regalo
        </button>
    </div>

    @if($event->gifts->isEmpty())
        <div class="text-center py-12 text-slate-500">
            <p>No has añadido regalos a la mesa todavía.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($event->gifts as $gift)
                <div class="bg-slate-900/50 p-4 rounded-lg border border-slate-700/50 flex justify-between items-start group">
                    <div class="flex-1 pr-4">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <h3 class="text-white font-medium">{{ $gift->name }}</h3>
                            @if($gift->store_label)
                                <span class="px-2 py-0.5 rounded text-[11px] bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                    {{ $gift->store_label }}
                                </span>
                            @endif
                            @if($gift->status === 'purchased')
                                <span class="px-2 py-0.5 rounded text-[11px] bg-green-500/10 text-green-400 border border-green-500/20">Comprado</span>
                            @elseif($gift->status === 'reserved')
                                <span class="px-2 py-0.5 rounded text-[11px] bg-yellow-500/10 text-yellow-400 border border-yellow-500/20">Reservado</span>
                            @endif
                        </div>

                        @if($gift->description)
                            <p class="text-sm text-slate-400 mb-1">{{ $gift->description }}</p>
                        @endif

                        <div class="flex items-center gap-4 text-xs text-slate-500 mt-1">
                            <span>Cantidad: <span class="text-slate-300">{{ $gift->quantity }}</span></span>
                            <span>Reservados: <span class="text-slate-300">{{ $gift->quantity_reserved }}</span></span>
                            @if($gift->url)
                                <a href="{{ $gift->url }}" target="_blank" rel="noopener" class="text-indigo-400 hover:text-indigo-300">Ver enlace ↗</a>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                        <button onclick='openEditGift(@json($gift))' class="p-2 text-slate-400 hover:text-indigo-400 transition-colors" title="Editar">
                            ✏️
                        </button>
                        <form action="{{ route('client.gifts.destroy', $gift->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este regalo?');">
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
<dialog id="modal-add-gift" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-lg">
        <h3 class="font-bold text-lg mb-4">Agregar Regalo</h3>
        <form action="{{ route('client.eventos.gifts.store', $event->id) }}" method="POST">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="label text-sm text-slate-400">Nombre <span class="text-red-400">*</span></label>
                    <input type="text" name="name" required class="input w-full" placeholder="Ej. Juego de sábanas, Licuadora...">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Descripción (Opcional)</label>
                    <textarea name="description" rows="2" class="input w-full" placeholder="Detalles adicionales del regalo..."></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-sm text-slate-400">Tienda (Opcional)</label>
                        <input type="text" name="store_label" class="input w-full" placeholder="Ej. Liverpool, Amazon">
                    </div>
                    <div>
                        <label class="label text-sm text-slate-400">Cantidad <span class="text-red-400">*</span></label>
                        <input type="number" name="quantity" value="1" min="1" required class="input w-full">
                    </div>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Enlace (Opcional)</label>
                    <input type="url" name="url" class="input w-full" placeholder="https://...">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Orden</label>
                    <input type="number" name="display_order" value="0" class="input w-full">
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" onclick="document.getElementById('modal-add-gift').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>Cerrar</button></form>
</dialog>

{{-- Edit Modal --}}
<dialog id="modal-edit-gift" class="modal">
    <div class="modal-box bg-slate-800 text-white max-w-lg">
        <h3 class="font-bold text-lg mb-4">Editar Regalo</h3>
        <form id="form-edit-gift" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="label text-sm text-slate-400">Nombre <span class="text-red-400">*</span></label>
                    <input type="text" name="name" id="edit-gift-name" required class="input w-full">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Descripción (Opcional)</label>
                    <textarea name="description" id="edit-gift-description" rows="2" class="input w-full"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-sm text-slate-400">Tienda (Opcional)</label>
                        <input type="text" name="store_label" id="edit-gift-store-label" class="input w-full">
                    </div>
                    <div>
                        <label class="label text-sm text-slate-400">Cantidad <span class="text-red-400">*</span></label>
                        <input type="number" name="quantity" id="edit-gift-quantity" min="1" required class="input w-full">
                    </div>
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Enlace (Opcional)</label>
                    <input type="url" name="url" id="edit-gift-url" class="input w-full">
                </div>

                <div>
                    <label class="label text-sm text-slate-400">Orden</label>
                    <input type="number" name="display_order" id="edit-gift-display-order" class="input w-full">
                </div>
            </div>

            <div class="modal-action mt-6">
                <button type="button" onclick="document.getElementById('modal-edit-gift').close()" class="btn btn-ghost">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>Cerrar</button></form>
</dialog>

<script>
function openEditGift(gift) {
    document.getElementById('edit-gift-name').value = gift.name ?? '';
    document.getElementById('edit-gift-description').value = gift.description ?? '';
    document.getElementById('edit-gift-store-label').value = gift.store_label ?? '';
    document.getElementById('edit-gift-quantity').value = gift.quantity ?? 1;
    document.getElementById('edit-gift-url').value = gift.url ?? '';
    document.getElementById('edit-gift-display-order').value = gift.display_order ?? 0;

    let url = '{{ route("client.gifts.update", ":id") }}';
    url = url.replace(':id', gift.id);
    document.getElementById('form-edit-gift').action = url;

    document.getElementById('modal-edit-gift').showModal();
}
</script>
