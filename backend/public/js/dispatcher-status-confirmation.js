(() => {
    const modal = document.getElementById('status-confirmation-modal');
    const form = document.getElementById('incident-status-form');
    const actions = document.getElementById('incident-status-actions');
    const notes = document.getElementById('status-modal-notes');
    const noteCounter = document.getElementById('status-note-counter');
    const noteError = document.getElementById('status-note-error');
    const modalError = document.getElementById('status-modal-error');
    const statusInput = document.getElementById('status-modal-value');
    const currentBadge = document.getElementById('status-modal-current');
    const nextBadge = document.getElementById('status-modal-next');
    const finalWarning = document.getElementById('status-final-warning');
    const confirmButton = document.getElementById('status-confirm-button');
    const currentStatusBadge = document.getElementById('incident-current-status');
    const toast = document.getElementById('incident-status-toast');
    const historyBody = document.querySelector('[data-status-history-body]');
    const transitions = {
        Reported: { status: 'Received', label: 'Acknowledge & Mark as Received', className: 'btn-status-received' },
        Received: { status: 'Dispatched', label: 'Dispatch Response Units', className: 'btn-status-dispatched' },
        Dispatched: { status: 'Completed', label: 'Mark as Completed / Resolved', className: 'btn-status-completed' },
    };
    const statusClass = status => `status-${status.toLowerCase()}`;
    let opener = null;
    let toastTimer = null;

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

    const updateTransition = (status, clearPreviousErrors = true) => {
        statusInput.value = status;
        nextBadge.className = `badge ${statusClass(status)}`;
        nextBadge.textContent = status;
        finalWarning.hidden = status !== 'Completed';
        if (clearPreviousErrors) {
            clearErrors();
        }
    };

    const openModal = (button, status = button.dataset.status, preserveErrors = false) => {
        opener = button;
        updateTransition(status, !preserveErrors);
        modal.hidden = false;
        document.body.classList.add('status-modal-open');
        requestAnimationFrame(() => notes.focus());
    };

    const closeModal = target => {
        modal.hidden = true;
        document.body.classList.remove('status-modal-open');
        const focusTarget = target || opener;
        if (focusTarget && document.contains(focusTarget)) {
            focusTarget.focus();
        }
        opener = null;
    };

    actions.addEventListener('click', event => {
        const button = event.target.closest('[data-status]');
        if (button) {
            openModal(button);
        }
    });

    modal.addEventListener('click', event => {
        if (event.target.closest('[data-modal-dismiss]') && !confirmButton.disabled) {
            closeModal();
        }
    });

    document.addEventListener('keydown', event => {
        if (modal.hidden) {
            return;
        }
        if (event.key === 'Escape' && !confirmButton.disabled) {
            event.preventDefault();
            closeModal();
            return;
        }
        if (event.key !== 'Tab') {
            return;
        }
        const focusable = [...modal.querySelectorAll('button:not(:disabled), textarea:not(:disabled)')];
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (!modal.contains(document.activeElement)) {
            event.preventDefault();
            first.focus();
        } else if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    notes.addEventListener('input', () => {
        noteCounter.textContent = `${notes.value.length} / 300 characters`;
        if (notes.value.trim()) {
            noteError.hidden = true;
            notes.removeAttribute('aria-invalid');
        }
    });

    const addHistoryEntry = data => {
        const emptyRow = historyBody.querySelector('[data-empty-history]');
        if (emptyRow) {
            emptyRow.remove();
        }
        const row = document.createElement('tr');
        const statusCell = document.createElement('td');
        const statusBadge = document.createElement('span');
        statusBadge.className = `badge ${statusClass(data.status)}`;
        statusBadge.textContent = data.status;
        statusCell.append(statusBadge);
        [statusCell, data.dispatcher, data.changed_at, data.notes].forEach((value, index) => {
            if (index === 0) {
                row.append(value);
            } else {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.append(cell);
            }
        });
        historyBody.prepend(row);
    };

    const updatePage = data => {
        currentStatusBadge.className = `badge ${statusClass(data.status)}`;
        currentStatusBadge.textContent = data.status;
        currentBadge.className = `badge ${statusClass(data.status)}`;
        currentBadge.textContent = data.status;
        addHistoryEntry(data);

        const next = transitions[data.status];
        if (!next) {
            actions.replaceChildren();
            const message = document.createElement('p');
            message.className = 'status-complete-message';
            message.textContent = 'This incident is complete. No further status changes are available.';
            actions.append(message);
            return currentStatusBadge;
        }

        const button = document.createElement('button');
        button.type = 'button';
        button.className = `btn-status-action ${next.className}`;
        button.dataset.status = next.status;
        button.textContent = next.label;
        actions.replaceChildren(button);
        return button;
    };

    const showToast = message => {
        toast.textContent = message;
        toast.hidden = false;
        window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(() => {
            toast.hidden = true;
        }, 5000);
    };

    form.addEventListener('submit', async event => {
        event.preventDefault();
        clearErrors();
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
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });
            const data = await response.json();
            if (!response.ok) {
                const noteMessage = data.errors?.notes?.[0];
                showError(noteMessage || data.errors?.status?.[0] || data.message || 'The status could not be updated.', Boolean(noteMessage));
                return;
            }

            const nextFocus = updatePage(data);
            closeModal(nextFocus);
            showToast(data.message);
        } catch {
            showError('The status could not be updated. Check your connection and try again.');
        } finally {
            confirmButton.disabled = false;
            modal.removeAttribute('aria-busy');
        }
    });

    noteCounter.textContent = `${notes.value.length} / 300 characters`;
    if (modal.dataset.openOnLoad === 'true') {
        const trigger = [...actions.querySelectorAll('[data-status]')]
            .find(button => button.dataset.status === statusInput.value)
            || actions.querySelector('[data-status]');
        if (trigger) {
            openModal(trigger, trigger.dataset.status, true);
        }
    }
    if (!toast.hidden) {
        window.setTimeout(() => {
            toast.hidden = true;
        }, 5000);
    }
})();
