(() => {
    const modal = document.getElementById('add-update-modal');
    const form = document.getElementById('incident-update-form');
    const trigger = document.getElementById('add-update-btn');
    const changedAtInput = document.getElementById('update-modal-changed-at');
    const changedDateInput = document.getElementById('update-modal-date');
    const changedTimeInput = document.getElementById('update-modal-time');
    const notes = document.getElementById('update-modal-notes');
    const noteCounter = document.getElementById('update-note-counter');
    const noteError = document.getElementById('update-note-error');
    const modalError = document.getElementById('update-modal-error');
    const confirmButton = document.getElementById('update-confirm-button');

    if (!modal || !form || !trigger || !notes || !noteCounter || !noteError || !modalError || !confirmButton) {
        return;
    }

    const currentLocalDateTime = () => {
        const now = new Date();
        const pad = value => String(value).padStart(2, '0');
        return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
    };

    let opener = null;

    const clearErrors = () => {
        noteError.hidden = true;
        modalError.hidden = true;
        notes.removeAttribute('aria-invalid');
        noteError.textContent = '';
        modalError.textContent = '';
    };

    const showError = (message, isNoteError = false) => {
        const target = isNoteError ? noteError : modalError;
        target.textContent = message;
        target.hidden = false;
        if (isNoteError) {
            notes.setAttribute('aria-invalid', 'true');
        }
    };

    const openModal = () => {
        opener = trigger;
        const now = currentLocalDateTime();
        const [date, time] = now.split('T');
        if (changedDateInput) changedDateInput.value = date;
        if (changedTimeInput) changedTimeInput.value = time;
        if (changedAtInput) changedAtInput.value = now;
        clearErrors();
        modal.hidden = false;
        document.body.classList.add('status-modal-open');
        const categorySelect = form.querySelector('select[name="category"]');
        requestAnimationFrame(() => (categorySelect || notes).focus());
    };

    const closeModal = () => {
        modal.hidden = true;
        document.body.classList.remove('status-modal-open');
        form.reset();
        noteCounter.textContent = '0 / 300 characters';
        clearErrors();
        if (opener && document.contains(opener)) {
            opener.focus();
        }
        opener = null;
    };

    trigger.addEventListener('click', () => {
        const currentStatus = document.getElementById('incident-current-status');
        if (currentStatus?.textContent?.trim() === 'Completed') {
            return;
        }
        openModal();
    });

    modal.addEventListener('click', event => {
        if ((event.target.closest('[data-modal-dismiss]') || event.target.closest('.status-modal-backdrop'))
            && !confirmButton.disabled) {
            closeModal();
        }
    });

    document.addEventListener('keydown', event => {
        if (modal.hidden || event.key !== 'Escape') return;
        event.preventDefault();
        if (!confirmButton.disabled) closeModal();
    });

    notes.addEventListener('input', () => {
        noteCounter.textContent = `${notes.value.length} / 300 characters`;
        if (notes.value.trim()) {
            noteError.hidden = true;
            notes.removeAttribute('aria-invalid');
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        clearErrors();

        const categorySelect = form.querySelector('select[name="category"]');
        if (!categorySelect?.value.trim()) {
            showError('Please select a category.');
            categorySelect?.focus();
            return;
        }
        if (!notes.value.trim()) {
            showError('A note is required.', true);
            notes.focus();
            return;
        }

        confirmButton.disabled = true;
        modal.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const noteMessage = data.errors?.notes?.[0];
                const categoryMessage = data.errors?.category?.[0];
                showError(noteMessage || categoryMessage || data.message || 'The update could not be saved.', Boolean(noteMessage));
                return;
            }

            window.dispatchEvent(new CustomEvent('resqlink:incident-history-entry', { detail: data }));
            closeModal();
            if (typeof window.showToast === 'function') {
                window.showToast({ type: 'success', message: data.message });
            }
        } catch {
            showError('The update could not be saved. Check your connection and try again.');
        } finally {
            confirmButton.disabled = false;
            modal.removeAttribute('aria-busy');
        }
    });

    noteCounter.textContent = `${notes.value.length} / 300 characters`;
})();
