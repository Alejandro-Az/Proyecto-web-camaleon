export default function initRsvpModule() {
    const section = document.querySelector('[data-module="rsvp"]');
    if (!section) return;

    if (section.dataset.rsvpInitialized === '1') return;
    section.dataset.rsvpInitialized = '1';

    const alerts = section.querySelector('[data-rsvp-alerts]');
    const summary = section.querySelector('[data-rsvp-summary]');
    const formWrap = section.querySelector('[data-rsvp-form-wrapper]');
    const form = section.querySelector('form[data-rsvp-form]');

    if (!form || !summary || !formWrap) return;

    const statusLabelEl = section.querySelector('[data-rsvp-status-label]');
    const confirmedRow = section.querySelector('[data-rsvp-confirmed-row]');
    const confirmedEl = section.querySelector('[data-rsvp-guests-confirmed]');
    const msgRow = section.querySelector('[data-rsvp-message-row]');
    const msgEl = section.querySelector('[data-rsvp-message]');

    const labels = {
        yes: 'Has confirmado tu asistencia.',
        no: 'Has indicado que no podrás asistir.',
        maybe: 'Has indicado que aún no estás seguro(a).',
        pending: 'Tu respuesta está pendiente.',
    };

    function showAlert(type, message) {
        if (!alerts) return;
        alerts.innerHTML = '';

        const div = document.createElement('div');
        div.className = 'rounded-2xl px-4 py-3 text-sm border ' + (
            type === 'error'
                ? 'bg-red-500/10 border-red-500/60 text-red-100'
                : 'bg-emerald-500/10 border-emerald-500/60 text-emerald-100'
        );
        div.textContent = message || (type === 'error' ? 'Ocurrió un error.' : 'Listo.');
        alerts.appendChild(div);

        setTimeout(() => div.remove(), 6000);
    }

    function openForm() {
        summary.classList.add('hidden');
        formWrap.classList.remove('hidden');
        formWrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function closeForm() {
        formWrap.classList.add('hidden');
        summary.classList.remove('hidden');
    }

    section.addEventListener('click', (e) => {
        const edit = e.target.closest('[data-rsvp-edit]');
        if (edit) {
            e.preventDefault();
            openForm();
            return;
        }

        const cancel = e.target.closest('[data-rsvp-cancel]');
        if (cancel) {
            e.preventDefault();
            closeForm();
            return;
        }
    });


    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : null;

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Guardando...';
        }

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });

            const data = await res.json();

            if (!res.ok) {
                // Laravel 422 trae errors
                if (data?.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    const firstMsg = data.errors[firstKey]?.[0] || data.message;
                    showAlert('error', firstMsg || 'Revisa los campos del formulario.');
                } else {
                    showAlert('error', data.message || 'No se pudo guardar.');
                }
                return;
            }

            // ✅ Actualizar resumen sin recargar
            const g = data.guest || {};
            const status = String(g.rsvp_status || '').toLowerCase();

            if (statusLabelEl) statusLabelEl.textContent = labels[status] || 'Tu respuesta ha sido registrada.';

            if (confirmedRow && confirmedEl) {
                if (status === 'yes') {
                    confirmedRow.classList.remove('hidden');
                    confirmedEl.textContent = String(g.guests_confirmed ?? 1);
                } else {
                    confirmedRow.classList.add('hidden');
                }
            }

            if (msgRow && msgEl) {
                const m = (g.rsvp_message || '').trim();
                if (m) {
                    msgRow.classList.remove('hidden');
                    msgEl.textContent = m;
                } else {
                    msgRow.classList.add('hidden');
                    msgEl.textContent = '';
                }
            }

            showAlert('success', data.message || 'Listo.');
            closeForm();
        } catch (err) {
            console.error(err);
            showAlert('error', 'No se pudo guardar. Verifica tu conexión.');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                if (originalText !== null) submitBtn.textContent = originalText;
            }
        }
    });
}
