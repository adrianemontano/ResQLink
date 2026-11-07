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
    const historyNotesModal = document.getElementById('history-notes-modal');
    const historyNotesContent = document.getElementById('history-notes-content');
    const addUpdateModal = document.getElementById('add-update-modal');
    const addUpdateForm = document.getElementById('incident-update-form');
    let addUpdateBtn = document.getElementById('add-update-btn');
    const updateNotes = document.getElementById('update-modal-notes');
    const updateNoteCounter = document.getElementById('update-note-counter');
    const updateNoteError = document.getElementById('update-note-error');
    const updateModalError = document.getElementById('update-modal-error');
    const updateConfirmButton = document.getElementById('update-confirm-button');
    const transitions = {
        Reported: { status: 'Received', label: 'Mark as received', className: 'btn-status-received' },
        Received: { status: 'Dispatched', label: 'Dispatch response units', className: 'btn-status-dispatched' },
        Dispatched: { status: 'Completed', label: 'Mark as completed', className: 'btn-status-completed' },
    };
    const statusClass = status => `status-${status.toLowerCase()}`;
    const statusOrder = ['Reported', 'Received', 'Dispatched', 'Completed'];

    const updateStepper = newStatus => {
        const stepper = document.querySelector('.status-stepper');
        if (!stepper) return;
        const newIndex = statusOrder.indexOf(newStatus);
        stepper.querySelectorAll('.stepper-step').forEach((step, index) => {
            step.classList.remove('stepper-past', 'stepper-current', 'stepper-future');
            if (index < newIndex) {
                step.classList.add('stepper-past');
            } else if (index === newIndex) {
                step.classList.add('stepper-current');
            } else {
                step.classList.add('stepper-future');
            }
        });
        stepper.querySelectorAll('.stepper-connector').forEach((conn, index) => {
            conn.classList.toggle('stepper-connector-past', index < newIndex);
        });
    };

    let opener = null;
    let notesOpener = null;
    let updateOpener = null;
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

    const openHistoryNotesModal = (button) => {
        notesOpener = button;
        const statusBadge = historyNotesModal.querySelector('#history-notes-status');
        const categorySpan = historyNotesModal.querySelector('#history-notes-category');
        const changedBy = historyNotesModal.querySelector('#history-notes-changed-by');
        const timestamp = historyNotesModal.querySelector('#history-notes-timestamp');

        if (button.dataset.entryType === 'update') {
            statusBadge.className = 'badge update-tag';
            statusBadge.textContent = 'Update';
            categorySpan.hidden = false;
            categorySpan.textContent = button.dataset.category || '';
        } else {
            statusBadge.textContent = button.dataset.status;
            statusBadge.className = `badge status-${button.dataset.status.toLowerCase()}`;
            categorySpan.hidden = true;
            categorySpan.textContent = '';
        }
        changedBy.textContent = button.dataset.changedBy;
        timestamp.textContent = `${button.dataset.date} at ${button.dataset.time}`;
        historyNotesContent.textContent = button.dataset.notes;

        historyNotesModal.hidden = false;
        document.body.classList.add('status-modal-open');
        requestAnimationFrame(() => {
            const closeBtn = historyNotesModal.querySelector('[data-modal-dismiss]');
            if (closeBtn) closeBtn.focus();
        });
    };

    const closeHistoryNotesModal = () => {
        historyNotesModal.hidden = true;
        document.body.classList.remove('status-modal-open');
        if (notesOpener && document.contains(notesOpener)) {
            notesOpener.focus();
        }
        notesOpener = null;
    };

    const openAddUpdateModal = (button) => {
        updateOpener = button;
        clearUpdateErrors();
        addUpdateModal.hidden = false;
        document.body.classList.add('status-modal-open');
        const categorySelect = addUpdateForm.querySelector('select[name="category"]');
        requestAnimationFrame(() => {
            if (categorySelect) {
                categorySelect.focus();
            } else {
                updateNotes.focus();
            }
        });
    };

    const closeAddUpdateModal = () => {
        addUpdateModal.hidden = true;
        document.body.classList.remove('status-modal-open');
        addUpdateForm.reset();
        updateNoteCounter.textContent = '0 / 300 characters';
        clearUpdateErrors();
        if (updateOpener && document.contains(updateOpener)) {
            updateOpener.focus();
        }
        updateOpener = null;
    };

    const clearUpdateErrors = () => {
        updateNoteError.hidden = true;
        updateModalError.hidden = true;
        updateNotes.removeAttribute('aria-invalid');
        updateNoteError.textContent = '';
        updateModalError.textContent = '';
    };

    const showUpdateError = (message, isNoteError = false) => {
        const target = isNoteError ? updateNoteError : updateModalError;
        target.textContent = message;
        target.hidden = false;
        if (isNoteError) {
            updateNotes.setAttribute('aria-invalid', 'true');
        }
    };

    actions.addEventListener('click', event => {
        const button = event.target.closest('[data-status]');
        if (button) {
            openModal(button);
        }
    });

    addUpdateBtn?.addEventListener('click', () => {
        openAddUpdateModal(addUpdateBtn);
    });

    modal.addEventListener('click', event => {
        if (event.target.closest('[data-modal-dismiss]') && !confirmButton.disabled) {
            closeModal();
        }
    });

    historyNotesModal.addEventListener('click', event => {
        if (event.target.closest('[data-modal-dismiss]') || event.target.closest('.status-modal-backdrop')) {
            closeHistoryNotesModal();
        }
    });

    addUpdateModal.addEventListener('click', event => {
        if (event.target.closest('[data-modal-dismiss]') || event.target.closest('.status-modal-backdrop')) {
            if (!updateConfirmButton.disabled) {
                closeAddUpdateModal();
            }
        }
    });

    document.addEventListener('keydown', event => {
        if (modal.hidden && historyNotesModal.hidden && addUpdateModal.hidden) {
            return;
        }
        if (event.key === 'Escape') {
            event.preventDefault();
            if (!modal.hidden && !confirmButton.disabled) {
                closeModal();
            } else if (!historyNotesModal.hidden) {
                closeHistoryNotesModal();
            } else if (!addUpdateModal.hidden && !updateConfirmButton.disabled) {
                closeAddUpdateModal();
            }
            return;
        }
        if (event.key !== 'Tab') {
            return;
        }
        const activeModal = !modal.hidden ? modal : (!historyNotesModal.hidden ? historyNotesModal : addUpdateModal);
        const focusable = [...activeModal.querySelectorAll('button:not(:disabled), textarea:not(:disabled), input:not(:disabled), select:not(:disabled)')];
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (!activeModal.contains(document.activeElement)) {
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

    updateNotes.addEventListener('input', () => {
        updateNoteCounter.textContent = `${updateNotes.value.length} / 300 characters`;
        if (updateNotes.value.trim()) {
            updateNoteError.hidden = true;
            updateNotes.removeAttribute('aria-invalid');
        }
    });

    const addHistoryEntry = data => {
        historyBody.querySelector('[data-empty-history]')?.remove();
        const row = document.createElement('tr');
        const isUpdate = data.entry_type === 'update';
        const statusHtml = isUpdate
            ? `<span class="badge update-tag">Update</span>`
            : `<span class="badge ${statusClass(data.status)}">${data.status}</span>`;

        const [date, ...restTime] = (data.changed_at || '').split(',');
        const time = restTime.join(',').trim();
        const safeNotes = (data.notes || '').replace(/"/g, '&quot;');
        const viewBtnHtml = data.notes
            ? `<button type="button" class="history-notes-view-btn" data-notes="${safeNotes}" data-status="${data.status}" data-entry-type="${data.entry_type || 'status_change'}" data-category="${data.category || ''}" data-changed-by="${data.dispatcher}" data-date="${date}" data-time="${time}" aria-label="View note for ${data.status} on ${date}">View</button>`
            : '';

        row.innerHTML = `<td>${statusHtml}</td><td>${data.dispatcher}</td><td>${data.changed_at}</td><td>${viewBtnHtml}</td>`;
        historyBody.prepend(row);
    };

    const updatePage = data => {
        if (data.entry_type === 'update') {
            addHistoryEntry(data);
            return null;
        }

        currentStatusBadge.className = `badge ${statusClass(data.status)}`;
        currentStatusBadge.textContent = data.status;
        currentBadge.className = `badge ${statusClass(data.status)}`;
        currentBadge.textContent = data.status;
        updateStepper(data.status);
        addHistoryEntry(data);

        // Update map marker if the function is available
        if (window.updateIncidentMapStatus && data.incident_id) {
            window.updateIncidentMapStatus(data.incident_id, data.status);
        }

        // Update map status pill if it exists
        const mapStatusPill = document.getElementById('map-status-pill');
        if (mapStatusPill) {
            mapStatusPill.querySelector('span').textContent = data.status;
            mapStatusPill.dataset.mapFilter = data.status;
        }

        const next = transitions[data.status];
        if (!next) {
            actions.replaceChildren();
            const message = document.createElement('p');
            message.className = 'status-complete-message';
            message.textContent = 'This incident is complete. No further updates are available.';
            actions.append(message);
            return currentStatusBadge;
        }

        const row = document.createElement('div');
        row.className = 'status-actions-row';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = `btn-status-action ${next.className}`;
        button.dataset.status = next.status;
        button.textContent = next.label;
        row.append(button);

        const addBtn = document.createElement('button');
        addBtn.type = 'button';
        addBtn.className = 'btn-add-update';
        addBtn.id = 'add-update-btn';
        addBtn.textContent = '+ Add update';
        row.append(addBtn);

        actions.replaceChildren(row);
        addUpdateBtn = addBtn;
        addUpdateBtn?.addEventListener('click', () => {
            openAddUpdateModal(addUpdateBtn);
        });
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
            let data = {};
            try {
                data = await response.json();
            } catch {
                data = {};
            }
            if (!response.ok) {
                const noteMessage = data.errors?.notes?.[0];
                showError(noteMessage || data.errors?.status?.[0] || data.message || 'The status could not be updated.', Boolean(noteMessage));
                return;
            }

            const nextFocus = updatePage(data);
            closeModal(nextFocus);
            window.dispatchEvent(new CustomEvent('resqlink:incident-status-updated'));
            showToast(data.message);
        } catch {
            showError('The status could not be updated. Check your connection and try again.');
        } finally {
            confirmButton.disabled = false;
            modal.removeAttribute('aria-busy');
        }
    });

    addUpdateForm.addEventListener('submit', async event => {
        event.preventDefault();
        clearUpdateErrors();

        const categorySelect = addUpdateForm.querySelector('select[name="category"]');
        const categoryValue = categorySelect ? categorySelect.value.trim() : '';
        if (!categoryValue) {
            showUpdateError('Please select a category.', false);
            if (categorySelect) categorySelect.focus();
            return;
        }
        if (!updateNotes.value.trim()) {
            showUpdateError('A note is required.', true);
            updateNotes.focus();
            return;
        }

        updateConfirmButton.disabled = true;
        addUpdateModal.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(addUpdateForm.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(addUpdateForm),
            });
            let data = {};
            try {
                data = await response.json();
            } catch {
                data = {};
            }
            if (!response.ok) {
                const noteMessage = data.errors?.notes?.[0];
                const categoryMessage = data.errors?.category?.[0];
                showUpdateError(noteMessage || categoryMessage || data.message || 'The update could not be saved. Check your connection and try again.', Boolean(noteMessage));
                return;
            }

            updatePage(data);
            closeAddUpdateModal();
            showToast(data.message);
        } catch {
            showUpdateError('The update could not be saved. Check your connection and try again.');
        } finally {
            updateConfirmButton.disabled = false;
            addUpdateModal.removeAttribute('aria-busy');
        }
    });

    noteCounter.textContent = `${notes.value.length} / 300 characters`;
    updateNoteCounter.textContent = `${updateNotes.value.length} / 300 characters`;
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

    // Handle history notes view button clicks
    historyBody?.addEventListener('click', function(event) {
        const viewButton = event.target.closest('.history-notes-view-btn');
        if (viewButton) {
            openHistoryNotesModal(viewButton);
        }
    });
})();
