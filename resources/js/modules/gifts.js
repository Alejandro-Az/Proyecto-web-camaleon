export default function initGiftsModule() {
    const section = document.querySelector('[data-module="gifts"]');
    if (!section) return;

    if (section.dataset.giftsInitialized === '1') return;
    section.dataset.giftsInitialized = '1';

    const slug = section.dataset.eventSlug || '';
    const requireCode = section.dataset.requireInvitationCode === '1';
    const allowUnclaim = section.dataset.allowUnclaim === '1';
    const allowMulti = section.dataset.allowMultiUnitReserve === '1';
    const showClaimers = section.dataset.showClaimers === '1';
    const maxUnitsPerGuest = parseInt(section.dataset.maxUnitsPerGuest || '1', 10) || 1;

    const summaryUrl = section.dataset.giftsSummaryUrl || '';
    const legacyMyClaimsUrl = section.dataset.myClaimsUrl || '';

    const successBox = section.querySelector('[data-gifts-success]');
    const errorBox = section.querySelector('[data-gifts-errors]');
    const errorsList = errorBox ? errorBox.querySelector('ul') : null;

    const codeBox = section.querySelector('[data-gifts-code-box]');
    const codeInput = section.querySelector('[data-gifts-code-input]');
    const codeSaveBtn = section.querySelector('[data-gifts-code-save]');
    const codeClearBtn = section.querySelector('[data-gifts-code-clear]');

    const storageKey = slug ? `camaleon_invitation_code_${slug}` : 'camaleon_invitation_code';

    function clearMessages() {
        if (successBox) {
            successBox.classList.add('hidden');
            successBox.textContent = '';
        }
        if (errorBox && errorsList) {
            errorBox.classList.add('hidden');
            errorsList.innerHTML = '';
        }
    }

    function showSuccess(message) {
        if (!successBox || !message) return;
        successBox.textContent = message;
        successBox.classList.remove('hidden');
        setTimeout(() => {
            successBox.classList.add('hidden');
            successBox.textContent = '';
        }, 6000);
    }

    function showErrors(messages) {
        if (!errorBox || !errorsList) return;
        errorsList.innerHTML = '';
        (messages || []).forEach((msg) => {
            const li = document.createElement('li');
            li.textContent = msg;
            errorsList.appendChild(li);
        });
        errorBox.classList.remove('hidden');
        setTimeout(() => {
            errorBox.classList.add('hidden');
            errorsList.innerHTML = '';
        }, 7000);
    }

    function getInvitationCode() {
        const fromDom = (section.dataset.invitationCode || '').trim();
        if (fromDom) return fromDom;

        try {
            const stored = (localStorage.getItem(storageKey) || '').trim();
            return stored;
        } catch {
            return '';
        }
    }

    function setInvitationCode(code) {
        const clean = (code || '').trim();
        section.dataset.invitationCode = clean;

        section.querySelectorAll('[data-gift-invitation-code]').forEach((input) => {
            input.value = clean;
        });

        try {
            if (clean) localStorage.setItem(storageKey, clean);
            else localStorage.removeItem(storageKey);
        } catch {
            // ignore
        }
    }

    function availabilityText(available, total) {
        if (available <= 0) return `No quedan unidades disponibles (${total} en total).`;
        if (available === 1) return `Queda 1 unidad disponible (de ${total}).`;
        return `Quedan ${available} unidades disponibles (de ${total}).`;
    }

    function statusLabelText(status, available, myQty) {
        if (myQty > 0) return 'Apartado por ti';
        if (status === 'purchased') return 'Comprado';
        if (available <= 0) return 'Sin unidades disponibles';
        return 'Disponible';
    }

    function updateClaimersList(card, claimers) {
        const ul = card.querySelector('[data-gift-claimers-list]');
        if (!ul) return;

        ul.innerHTML = '';

        if (!Array.isArray(claimers) || claimers.length === 0) {
            const li = document.createElement('li');
            li.className = 'text-slate-400';
            li.textContent = '—';
            ul.appendChild(li);
            return;
        }

        claimers.forEach((c) => {
            const name = (c && c.name) ? c.name : 'Invitado';
            const qty = (c && typeof c.quantity !== 'undefined') ? Number(c.quantity) : 0;

            const li = document.createElement('li');
            li.textContent = qty > 1 ? `${name} (${qty})` : name;
            ul.appendChild(li);
        });
    }

    function updateCardFromGift(card, giftData) {
        const total = Number(giftData.quantity || 1);
        const reserved = Number(giftData.quantity_reserved || 0);
        const available = Number(giftData.available_units ?? Math.max(0, total - reserved));
        const status = giftData.status || '';
        const myQty = Number(giftData.my_claim_quantity || 0);
        const maxUnits = Number(giftData.max_units_per_guest || maxUnitsPerGuest) || maxUnitsPerGuest;

        card.dataset.giftTotal = String(total);
        card.dataset.giftReserved = String(reserved);
        card.dataset.giftAvailable = String(available);
        card.dataset.giftStatus = String(status);
        card.dataset.giftMyQty = String(myQty);

        const statusLabel = card.querySelector('[data-gift-status-label]');
        if (statusLabel) {
            statusLabel.textContent = statusLabelText(status, available, myQty);
        }

        const availText = card.querySelector('[data-gift-availability-text]');
        if (availText) {
            availText.textContent = availabilityText(available, total);
        }

        const myQtyText = card.querySelector('[data-gift-my-qty-text]');
        if (myQtyText) {
            if (myQty > 0) {
                myQtyText.classList.remove('hidden');
                myQtyText.textContent = `Usted apartó ${myQty} / ${maxUnits} unidad(es).`;
            } else {
                myQtyText.classList.add('hidden');
                myQtyText.textContent = '';
            }
        }

        const reserveForm = card.querySelector('[data-gift-form="reserve"]');
        const unreserveForm = card.querySelector('[data-gift-form="unreserve"]');

        const hasCode = !!getInvitationCode();
        const canReserve =
            status !== 'purchased' &&
            available > 0 &&
            (!requireCode || hasCode) &&
            myQty < maxUnits;

        const canUnreserve =
            allowUnclaim &&
            status !== 'purchased' &&
            myQty > 0 &&
            (!requireCode || hasCode);

        if (reserveForm) {
            reserveForm.classList.toggle('hidden', !canReserve);

            const qtyInput = reserveForm.querySelector('[data-gift-quantity-input]');
            if (qtyInput) {
                const remainingForGuest = Math.max(0, maxUnits - myQty);
                const maxPossible = Math.max(1, Math.min(available, remainingForGuest));
                qtyInput.max = String(maxPossible);

                const currentVal = Number(qtyInput.value || 1);
                if (currentVal < 1) qtyInput.value = '1';
                if (currentVal > maxPossible) qtyInput.value = String(maxPossible);
            }
        }

        if (unreserveForm) {
            unreserveForm.classList.toggle('hidden', !canUnreserve);
        }

        if (showClaimers && giftData.claimers) {
            updateClaimersList(card, giftData.claimers);
        }
    }

    async function refreshSummary() {
        const code = getInvitationCode();

        const url = summaryUrl
            ? `${summaryUrl}?invitation_code=${encodeURIComponent(code)}`
            : (legacyMyClaimsUrl ? `${legacyMyClaimsUrl}?invitation_code=${encodeURIComponent(code)}` : '');

        if (!url) return;

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) return;

            const data = await response.json();

            if (data && Array.isArray(data.gifts)) {
                data.gifts.forEach((g) => {
                    const card = section.querySelector(`[data-gift-card][data-gift-id="${g.gift_id}"]`);
                    if (card) updateCardFromGift(card, g);
                });

                if (codeBox && code) {
                    codeBox.classList.add('hidden');
                }

                return;
            }

            if (data && data.claims) {
                Object.entries(data.claims).forEach(([giftId, qty]) => {
                    const card = section.querySelector(`[data-gift-card][data-gift-id="${giftId}"]`);
                    if (!card) return;

                    card.dataset.giftMyQty = String(Number(qty || 0));

                    const myQtyText = card.querySelector('[data-gift-my-qty-text]');
                    if (myQtyText) {
                        const my = Number(qty || 0);
                        if (my > 0) {
                            myQtyText.classList.remove('hidden');
                            myQtyText.textContent = `Usted apartó ${my} / ${maxUnitsPerGuest} unidad(es).`;
                        } else {
                            myQtyText.classList.add('hidden');
                            myQtyText.textContent = '';
                        }
                    }
                });
            }
        } catch {
            // ignore
        }
    }

    async function submitGiftForm(form) {
        clearMessages();

        const code = getInvitationCode();
        if (requireCode && !code) {
            showErrors(['Para apartar regalos necesita su código de invitación.']);
            return;
        }

        form.querySelectorAll('[data-gift-invitation-code]').forEach((input) => {
            input.value = code;
        });

        const formData = new FormData(form);

        const btn = form.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            let data = null;
            try { data = await response.json(); } catch { data = null; }

            if (response.status === 429) {
                showErrors(['Demasiadas solicitudes. Espere un momento e intente de nuevo.']);
                return;
            }

            if (!response.ok) {
                const msgs = [];

                if (data && data.message) msgs.push(data.message);

                if (data && data.errors) {
                    Object.values(data.errors).forEach((arr) => {
                        if (Array.isArray(arr)) {
                            arr.forEach((msg) => msgs.push(msg));
                        }
                    });
                }

                if (!msgs.length) msgs.push('No se pudo completar la acción. Intente nuevamente.');
                showErrors(msgs);
                return;
            }

            showSuccess((data && data.message) || 'Acción realizada correctamente.');

            const card = form.closest('[data-gift-card]');
            if (card && data && data.gift_id) {
                updateCardFromGift(card, data);
            }

            await refreshSummary();
        } catch {
            showErrors(['Ocurrió un error de conexión. Revise su internet e intente nuevamente.']);
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    section.querySelectorAll('[data-gift-form="reserve"]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitGiftForm(form);
        });
    });

    section.querySelectorAll('[data-gift-form="unreserve"]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            submitGiftForm(form);
        });
    });

    if (codeSaveBtn && codeInput) {
        codeSaveBtn.addEventListener('click', async () => {
            clearMessages();
            const code = (codeInput.value || '').trim();
            if (!code) {
                showErrors(['Pegue un código válido.']);
                return;
            }
            setInvitationCode(code);
            showSuccess('Código guardado.');
            if (codeBox) codeBox.classList.add('hidden');
            await refreshSummary();
        });
    }

    if (codeClearBtn) {
        codeClearBtn.addEventListener('click', async () => {
            clearMessages();
            setInvitationCode('');
            showSuccess('Código removido.');
            if (codeBox) codeBox.classList.remove('hidden');
            await refreshSummary();
        });
    }

    const initialCode = getInvitationCode();
    if (initialCode) {
        setInvitationCode(initialCode);
        if (codeBox) codeBox.classList.add('hidden');
    }

    refreshSummary();
}
