<div style="padding: 80px 24px; background: var(--surface-alt);">
    <div style="max-width: 1100px; margin: 0 auto;">
        <div style="text-align: center; margin-bottom: 50px;">
            <div class="cml-eyebrow" style="margin-bottom: 14px;">Código de vestimenta</div>
            <h2 class="cml-serif text-4xl italic text-[var(--ink)]">Etiqueta</h2>
            <div class="cml-divider max-w-[80px] mx-auto mt-6 text-[var(--accent)]"><i data-lucide="leaf" class="w-4 h-4 mx-auto"></i></div>
        </div>

        @if($dressCodes->isEmpty())
            <p class="text-sm text-[var(--ink-muted)] text-center">No hay información de código de vestimenta.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 text-center">
                @foreach($dressCodes as $code)
                    <div>
                        <div style="width: 56px; height: 56px; border-radius: 50%; border: 1px solid var(--accent); display: inline-flex; align-items: center; justify-content: center; color: var(--accent); margin-bottom: 16px;">
                            <i data-lucide="shirt" class="w-6 h-6"></i>
                        </div>
                        <h3 class="cml-eyebrow" style="font-size: 10.5px; margin-bottom: 10px;">{{ $code->type_label }}</h3>
                        <p class="cml-sans" style="font-size: 13px; line-height: 1.6; color: var(--ink-soft); max-width: 260px; margin: 0 auto;">
                            {{ $code->description }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>