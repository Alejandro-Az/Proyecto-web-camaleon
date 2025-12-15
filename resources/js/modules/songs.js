/**
 * MÓDULO: Sugerencias y votos de canciones
 *
 * Este archivo NO debe importar bootstrap ni registrar DOMContentLoaded.
 * Solo exporta una función initSongsModule() para que app.js la invoque.
 */
export default function initSongsModule() {
    const songsSection = document.querySelector('[data-module="songs"]');
    if (!songsSection) return;

    // Evitar doble inicialización (por si algo llama init 2 veces)
    if (songsSection.dataset.songsInitialized === '1') return;
    songsSection.dataset.songsInitialized = '1';

    const alertsContainer = songsSection.querySelector('[data-song-alerts]');
    const suggestionForm  = songsSection.querySelector('[data-song-form="suggestion"]');
    const suggestionBtn   = songsSection.querySelector('[data-song-submit="suggestion"]');
    const songsList       = songsSection.querySelector('[data-song-list]');
    const emptyMessage    = songsSection.querySelector('[data-song-list-empty]');

    function showAlert(type, message) {
        if (!alertsContainer || !message) return;

        const baseClasses = 'rounded-2xl px-4 py-3 text-sm border mb-2';

        let extraClasses = '';
        switch (type) {
            case 'error':
                extraClasses = ' bg-red-500/10 border-red-500/60 text-red-100';
                break;
            case 'info':
                extraClasses = ' bg-sky-500/10 border-sky-500/60 text-sky-100';
                break;
            default:
                extraClasses = ' bg-emerald-500/10 border-emerald-500/60 text-emerald-100';
        }

        const div = document.createElement('div');
        div.className = baseClasses + extraClasses;
        div.textContent = message;

        alertsContainer.appendChild(div);

        setTimeout(() => {
            div.remove();
        }, 6000);
    }

    async function handleSuggestionSubmit(event) {
        event.preventDefault();
        if (!suggestionForm) return;

        const formData = new FormData(suggestionForm);

        if (suggestionBtn) {
            suggestionBtn.disabled = true;
            suggestionBtn.textContent = 'Enviando...';
        }

        try {
            const response = await fetch(suggestionForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                showAlert('error', data.message || 'Ocurrió un error al guardar la canción.');
                return;
            }

            showAlert('success', data.message || 'Canción guardada correctamente.');

            // Limpiar formulario
            suggestionForm.reset();

            if (emptyMessage) {
                emptyMessage.remove();
            }

            if (songsList && data.song) {
                const li = document.createElement('li');
                li.className = 'py-3 flex flex-col gap-2';
                li.setAttribute('data-song-item-id', data.song.id);

                li.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-100">
                                ${data.song.title}
                            </p>
                            ${data.song.artist ? `
                                <p class="text-xs text-slate-300">
                                    ${data.song.artist}
                                </p>` : ''}

                            ${data.song.message_for_couple ? `
                                <p class="text-xs text-slate-300 mt-1 italic">
                                    "${data.song.message_for_couple}"
                                </p>` : ''}

                            ${data.song.suggested_by_name ? `
                                <p class="text-xs text-slate-400 mt-1">
                                    Sugerida por <span class="font-semibold">${data.song.suggested_by_name}</span>
                                </p>` : ''}

                            ${data.song.url ? `
                                <a href="${data.song.url}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center text-[11px] text-sky-300 hover:text-sky-200 mt-1 underline">
                                    Escuchar / ver enlace
                                </a>` : ''}
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-slate-300 mb-1" data-song-votes>
                                ${data.song.votes_count} votos
                            </p>
                            <p class="text-[11px] text-slate-400">
                                Use su enlace de invitación<br>para votar.
                            </p>
                        </div>
                    </div>
                `;

                songsList.appendChild(li);

                // Si su UI agrega forms de voto dentro de li, aquí se podría bindear el submit.
                // Si no, ignore.
                li.querySelectorAll('[data-song-form="vote"]').forEach((form) => {
                    form.addEventListener('submit', handleVoteSubmit);
                });
            }
        } catch (error) {
            console.error(error);
            showAlert('error', 'No se pudo enviar la sugerencia. Verifique su conexión.');
        } finally {
            if (suggestionBtn) {
                suggestionBtn.disabled = false;
                suggestionBtn.textContent = 'Enviar sugerencia';
            }
        }
    }

    async function handleVoteSubmit(event) {
        event.preventDefault();

        const form = event.currentTarget;
        const songItem = form.closest('[data-song-item-id]');
        if (!songItem) return;

        const votesLabel = songItem.querySelector('[data-song-votes]');
        const button = songItem.querySelector('[data-song-vote-button]');

        const formData = new FormData(form);

        if (button) {
            button.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await response.json();

            if (!response.ok) {
                showAlert('error', data.message || 'No se pudo registrar el voto.');
                return;
            }

            if (votesLabel && typeof data.votes_count !== 'undefined') {
                const votos = data.votes_count;
                votesLabel.textContent = `${votos} ${votos === 1 ? 'voto' : 'votos'}`;
            }

            if (button && typeof data.has_voted !== 'undefined') {
                if (data.has_voted) {
                    button.textContent = 'Quitar mi voto';
                    button.classList.remove('bg-sky-500', 'hover:bg-sky-400', 'text-white');
                    button.classList.add('bg-slate-700', 'hover:bg-slate-600', 'text-slate-100');
                } else {
                    button.textContent = 'Votar por esta canción';
                    button.classList.remove('bg-slate-700', 'hover:bg-slate-600', 'text-slate-100');
                    button.classList.add('bg-sky-500', 'hover:bg-sky-400', 'text-white');
                }
            }

            showAlert('success', data.message || 'Voto actualizado.');
        } catch (error) {
            console.error(error);
            showAlert('error', 'No se pudo enviar el voto. Verifique su conexión.');
        } finally {
            if (button) {
                button.disabled = false;
            }
        }
    }

    if (suggestionForm) {
        suggestionForm.addEventListener('submit', handleSuggestionSubmit);
    }

    songsSection.querySelectorAll('[data-song-form="vote"]').forEach((form) => {
        form.addEventListener('submit', handleVoteSubmit);
    });
}
