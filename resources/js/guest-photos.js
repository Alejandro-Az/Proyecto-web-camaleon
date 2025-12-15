document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-guest-photos-form]');
    if (!form) {
        return;
    }

    // Si el navegador no tiene fetch/FormData, dejamos comportamiento normal (fallback)
    if (!window.fetch || !window.FormData) {
        return;
    }

    const successBox = document.querySelector('[data-guest-photos-success]');
    const errorBox   = document.querySelector('[data-guest-photos-errors]');
    const errorsList = errorBox ? errorBox.querySelector('ul') : null;
    const grid       = document.querySelector('[data-guest-photos-grid]');

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        // Limpiar mensajes previos
        if (successBox) {
            successBox.classList.add('hidden');
            successBox.textContent = '';
        }
        if (errorBox && errorsList) {
            errorBox.classList.add('hidden');
            errorsList.innerHTML = '';
        }

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: (form.method || 'POST').toUpperCase(),
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            let data = null;
            try {
                data = await response.json();
            } catch (e) {
                // si no hay JSON, data queda null
            }

            if (!response.ok) {
                // Errores de validación / negocio
                const messages = [];

                if (data && data.message) {
                    messages.push(data.message);
                }

                if (data && data.errors) {
                    Object.values(data.errors).forEach((arr) => {
                        if (Array.isArray(arr)) {
                            arr.forEach((msg) => messages.push(msg));
                        }
                    });
                }

                if (!messages.length) {
                    messages.push('No se pudo subir la foto. Intente de nuevo.');
                }

                if (errorBox && errorsList) {
                    messages.forEach((msg) => {
                        const li = document.createElement('li');
                        li.textContent = msg;
                        errorsList.appendChild(li);
                    });
                    errorBox.classList.remove('hidden');
                }

                return;
            }

            // Éxito
            const messageText = (data && data.message) || 'Foto subida correctamente.';
            if (successBox) {
                successBox.textContent = messageText;
                successBox.classList.remove('hidden');
            }

            // Si fue auto-aprobada, la agregamos al grid en caliente
            if (data && data.auto_approved && data.file_url && grid) {
                const figure = document.createElement('figure');
                figure.className = 'relative rounded-2xl overflow-hidden bg-slate-900/60 border border-slate-700';

                const img = document.createElement('img');
                img.src = data.file_url;
                img.alt = data.caption || 'Foto de invitado';
                img.loading = 'lazy';
                img.className = 'w-full h-40 object-cover';
                figure.appendChild(img);

                if (data.caption) {
                    const figcap = document.createElement('figcaption');
                    figcap.className = 'absolute inset-x-0 bottom-0 bg-slate-900/70 px-2 py-1 text-[11px] text-slate-100';
                    figcap.textContent = data.caption;
                    figure.appendChild(figcap);
                }

                // Quitar texto "aún no hay fotos" si existe
                const empty = document.querySelector('[data-guest-photos-empty]');
                if (empty) {
                    empty.remove();
                }

                // Insertamos al inicio del grid
                grid.prepend(figure);
            }

            // Limpiar inputs de archivo y caption (no tocamos invitation_code)
            const photoInput = form.querySelector('input[name="photo"]');
            if (photoInput) {
                photoInput.value = '';
            }
            const captionInput = form.querySelector('input[name="caption"]');
            if (captionInput) {
                captionInput.value = '';
            }
        } catch (error) {
            if (errorBox && errorsList) {
                const li = document.createElement('li');
                li.textContent = 'Ocurrió un error al subir la foto. Revise su conexión e intente nuevamente.';
                errorsList.appendChild(li);
                errorBox.classList.remove('hidden');
            }
        }
    });
});
