(() => {
    const modal = document.getElementById('status-confirmation-modal');
    const form = document.getElementById('incident-status-form');
    const actions = document.getElementById('incident-status-actions');
    const statusSelect = document.getElementById('status-select');
    const statusTrigger = document.getElementById('status-update-trigger');
    const notes = document.getElementById('status-modal-notes');
    const noteCounter = document.getElementById('status-note-counter');
    const noteError = document.getElementById('status-note-error');
    const modalError = document.getElementById('status-modal-error');
    const changedAtInput = document.getElementById('status-modal-changed-at');
    const changedDateInput = document.getElementById('status-modal-date');
    const changedTimeInput = document.getElementById('status-modal-time');
    const changedAtError = document.getElementById('status-changed-at-error');
    const teamField = document.getElementById('status-team-field');
    const teamSelect = document.getElementById('status-modal-team');
    const externalTeamInput = document.getElementById('status-modal-external-team');
    const teamError = document.getElementById('status-team-error');
    const severitySelect = document.getElementById('status-modal-severity');
    const statusInput = document.getElementById('status-modal-value');
    const currentBadge = document.getElementById('status-modal-current');
    const nextBadge = document.getElementById('status-modal-next');
    const finalWarning = document.getElementById('status-final-warning');
    const confirmButton = document.getElementById('status-confirm-button');
    const currentStatusBadge = document.getElementById('incident-current-status');
    const currentSeverityBadge = document.getElementById('incident-current-severity');
    const dispatchedTeamDisplay = document.getElementById('incident-dispatched-team');
    const historyBody = document.querySelector('[data-status-history-body]');
    const historyNotesModal = document.getElementById('history-notes-modal');
    const historyNotesEditInput = document.getElementById('history-notes-edit-input');
    const historyNotesEditError = document.getElementById('history-notes-edit-error');
    const historyNotesEditBtn = document.getElementById('history-notes-edit-btn');
    const historyNotesSaveBtn = document.getElementById('history-notes-save-btn');
    const historyNotesCancelBtn = document.getElementById('history-notes-cancel-btn');
    const historyNotesCloseBtn = document.getElementById('history-notes-close-btn');
    const addUpdateModal = document.getElementById('add-update-modal');
    const addUpdateForm = document.getElementById('incident-update-form');
    const addUpdateBtn = document.getElementById('add-update-btn');
    const updateChangedAtInput = document.getElementById('update-modal-changed-at');
    const updateDateInput = document.getElementById('update-modal-date');
    const updateTimeInput = document.getElementById('update-modal-time');
    const updateNotes = document.getElementById('update-modal-notes');
    const updateNoteCounter = document.getElementById('update-note-counter');
    const updateNoteError = document.getElementById('update-note-error');
    const updateModalError = document.getElementById('update-modal-error');
    const updateConfirmButton = document.getElementById('update-confirm-button');

    if (!modal || !form || !actions) return;

    const statusClass = status => `status-${String(status || 'reported').toLowerCase()}`;
    const severityClass = sev => `severity-${String(sev || 'unrated').toLowerCase()}`;
    const statusOrder = ['Reported', 'Received', 'Dispatched', 'Completed'];

    const currentLocalDateTime = () => {
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        return `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
    };

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

    const clearErrors = () => {
        [noteError, modalError, changedAtError, teamError].forEach(el => {
            if (!el) return;
            el.hidden = true;
            el.textContent = '';
        });
        notes?.removeAttribute('aria-invalid');
        changedAtInput?.removeAttribute('aria-invalid');
        teamSelect?.removeAttribute('aria-invalid');
    };

    const showFieldError = (errorEl, inputEl, message) => {
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.hidden = false;
        }
        inputEl?.setAttribute('aria-invalid', 'true');
    };

    const updateTransition = (status, clearPreviousErrors = true) => {
        statusInput.value = status;
        nextBadge.className = `badge ${statusClass(status)}`;
        nextBadge.textContent = status;
        finalWarning.hidden = status !== 'Completed';
        if (teamField) {
            teamField.hidden = status !== 'Dispatched';
            if (status === 'Dispatched' && clearPreviousErrors && currentStatusBadge?.textContent?.trim() === 'Dispatched') {
                if (teamSelect) teamSelect.value = '';
                if (externalTeamInput) externalTeamInput.value = '';
                const teamSearch = document.getElementById('status-modal-team-search');
                if (teamSearch) teamSearch.value = '';
            }
        }
        if (clearPreviousErrors) {
            clearErrors();
        }
    };

    const openModal = (button, status = statusSelect?.value || button?.dataset?.status || 'Received', preserveErrors = false) => {
        // Block status changes on completed incidents
        const currentStatusBadge = document.getElementById('incident-current-status');
        if (currentStatusBadge && currentStatusBadge.textContent?.trim() === 'Completed') {
            return;
        }

        opener = button;
        if (changedAtInput && (!preserveErrors || !changedAtInput.value)) {
            const nowStr = currentLocalDateTime();
            const [d, t] = nowStr.split('T');
            if (changedDateInput && !changedDateInput.value) changedDateInput.value = d;
            if (changedTimeInput && !changedTimeInput.value) changedTimeInput.value = t;
            changedAtInput.value = (changedDateInput?.value && changedTimeInput?.value)
                ? `${changedDateInput.value}T${changedTimeInput.value}`
                : nowStr;
        }
        updateTransition(status, !preserveErrors);
        modal.hidden = false;
        document.body.classList.add('status-modal-open');
        requestAnimationFrame(() => notes?.focus());
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

    const openHistoryNotesModal = button => {
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
            statusBadge.className = `badge ${statusClass(button.dataset.status)}`;
            categorySpan.hidden = true;
            categorySpan.textContent = '';
        }
        changedBy.textContent = button.dataset.changedBy;
        timestamp.textContent = `${button.dataset.date} at ${button.dataset.time}`;
        if (historyNotesEditInput) {
            historyNotesEditInput.value = button.dataset.notes || '';
            historyNotesEditInput.disabled = true;
        }
        if (historyNotesEditError) {
            historyNotesEditError.hidden = true;
            historyNotesEditError.textContent = '';
        }
        if (historyNotesEditBtn) historyNotesEditBtn.hidden = !button.dataset.updateUrl;
        if (historyNotesSaveBtn) historyNotesSaveBtn.hidden = true;
        if (historyNotesCancelBtn) historyNotesCancelBtn.hidden = true;
        if (historyNotesCloseBtn) historyNotesCloseBtn.hidden = false;

        historyNotesModal.hidden = false;
        document.body.classList.add('status-modal-open');
        requestAnimationFrame(() => {
            historyNotesModal.querySelector('[data-modal-dismiss]')?.focus();
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

    historyNotesEditBtn?.addEventListener('click', () => {
        if (!historyNotesEditInput) return;
        historyNotesEditInput.value = notesOpener?.dataset?.notes || '';
        historyNotesEditInput.disabled = false;
        historyNotesEditBtn.hidden = true;
        if (historyNotesCloseBtn) historyNotesCloseBtn.hidden = true;
        if (historyNotesSaveBtn) historyNotesSaveBtn.hidden = false;
        if (historyNotesCancelBtn) historyNotesCancelBtn.hidden = false;
        historyNotesEditInput.focus();
    });

    historyNotesCancelBtn?.addEventListener('click', () => {
        if (historyNotesEditInput) {
            historyNotesEditInput.value = notesOpener?.dataset?.notes || '';
            historyNotesEditInput.disabled = true;
        }
        if (historyNotesEditError) historyNotesEditError.hidden = true;
        if (historyNotesSaveBtn) historyNotesSaveBtn.hidden = true;
        if (historyNotesCancelBtn) historyNotesCancelBtn.hidden = true;
        if (historyNotesEditBtn) historyNotesEditBtn.hidden = !notesOpener?.dataset?.updateUrl;
        if (historyNotesCloseBtn) historyNotesCloseBtn.hidden = false;
    });

    historyNotesSaveBtn?.addEventListener('click', async () => {
        const updateUrl = notesOpener?.dataset?.updateUrl;
        if (!updateUrl || !historyNotesEditInput) return;
        const originalNotes = (notesOpener?.dataset?.notes || '').trim();
        const newNotes = historyNotesEditInput.value.trim();
        if (!newNotes) {
            if (historyNotesEditError) {
                historyNotesEditError.textContent = 'Note cannot be empty.';
                historyNotesEditError.hidden = false;
            }
            historyNotesEditInput.focus();
            return;
        }
        if (newNotes === originalNotes) {
            historyNotesEditInput.disabled = true;
            historyNotesSaveBtn.hidden = true;
            if (historyNotesCancelBtn) historyNotesCancelBtn.hidden = true;
            if (historyNotesEditBtn) historyNotesEditBtn.hidden = false;
            if (historyNotesCloseBtn) historyNotesCloseBtn.hidden = false;
            if (historyNotesEditError) historyNotesEditError.hidden = true;
            window.showToast?.({ type: 'info', message: 'No changes made.' });
            return;
        }
        historyNotesSaveBtn.disabled = true;
        try {
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const res = await fetch(updateUrl, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ notes: newNotes }),
            });
            const isJson = res.headers.get('content-type')?.includes('application/json');
            const data = isJson ? await res.json() : null;
            if (!res.ok) {
                throw new Error(data?.message || `Failed to update note (HTTP ${res.status}).`);
            }
            if (notesOpener) notesOpener.dataset.notes = data?.notes ?? newNotes;
            historyNotesEditInput.value = data?.notes ?? newNotes;
            historyNotesEditInput.disabled = true;
            historyNotesSaveBtn.hidden = true;
            if (historyNotesCancelBtn) historyNotesCancelBtn.hidden = true;
            if (historyNotesEditBtn) historyNotesEditBtn.hidden = false;
            if (historyNotesCloseBtn) historyNotesCloseBtn.hidden = false;
            if (historyNotesEditError) historyNotesEditError.hidden = true;
            window.showToast?.({ type: 'success', message: data?.message || 'Note updated successfully.' });
        } catch (err) {
            const msg = err.message || 'Unable to save note.';
            if (historyNotesEditError) {
                historyNotesEditError.textContent = msg;
                historyNotesEditError.hidden = false;
            }
            window.showToast?.({ type: 'error', message: msg });
        } finally {
            historyNotesSaveBtn.disabled = false;
        }
    });

    const openAddUpdateModal = button => {
        updateOpener = button;
        clearUpdateErrors();
        if (updateChangedAtInput) {
            const nowStr = currentLocalDateTime();
            const [d, t] = nowStr.split('T');
            if (updateDateInput) updateDateInput.value = d;
            if (updateTimeInput) updateTimeInput.value = t;
            updateChangedAtInput.value = nowStr;
        }
        addUpdateModal.hidden = false;
        document.body.classList.add('status-modal-open');
        const categorySelect = addUpdateForm.querySelector('select[name="category"]');
        requestAnimationFrame(() => (categorySelect || updateNotes)?.focus());
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

    statusSelect?.addEventListener('change', () => {
        const chosenStatus = statusSelect.value;
        if (!chosenStatus) return;
        openModal(statusSelect, chosenStatus);
        statusSelect.value = '';
    });

    const statusToggle = document.getElementById('update-status-toggle');
    const statusMenu = document.getElementById('status-dropdown-menu');

    if (statusToggle && statusMenu) {
        statusToggle.addEventListener('click', (e) => {
            // Block on completed incidents
            const currentStatusBadge = document.getElementById('incident-current-status');
            if (currentStatusBadge && currentStatusBadge.textContent?.trim() === 'Completed') {
                e.preventDefault();
                return;
            }
            e.stopPropagation();
            const isHidden = !statusMenu.classList.contains('visible');
            if (isHidden) {
                statusMenu.classList.add('visible');
            } else {
                statusMenu.classList.remove('visible');
            }
            statusToggle.setAttribute('aria-expanded', String(isHidden));
        });

        document.addEventListener('click', (e) => {
            if (statusMenu.classList.contains('visible') && !statusMenu.contains(e.target) && e.target !== statusToggle) {
                statusMenu.classList.remove('visible');
                statusToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    document.addEventListener('click', (e) => {
        const actionBtn = e.target.closest('[data-action="status"], .btn-status-action');
        if (actionBtn && actionBtn.dataset.status) {
            // Block on completed incidents
            const currentStatusBadge = document.getElementById('incident-current-status');
            if (currentStatusBadge && currentStatusBadge.textContent?.trim() === 'Completed') {
                e.preventDefault();
                return;
            }
            openModal(actionBtn, actionBtn.dataset.status);
        }
    });

    addUpdateBtn?.addEventListener('click', () => {
        // Block on completed incidents
        const currentStatusBadge = document.getElementById('incident-current-status');
        if (currentStatusBadge && currentStatusBadge.textContent?.trim() === 'Completed') {
            return;
        }
        openAddUpdateModal(addUpdateBtn);
    });

    modal.addEventListener('click', event => {
        if (event.target.closest('[data-modal-dismiss]') && !confirmButton.disabled) {
            closeModal();
        }
    });

    historyNotesModal?.addEventListener('click', event => {
        if (event.target.closest('[data-modal-dismiss]') || event.target.closest('.status-modal-backdrop')) {
            closeHistoryNotesModal();
        }
    });

    addUpdateModal?.addEventListener('click', event => {
        if (event.target.closest('[data-modal-dismiss]') || event.target.closest('.status-modal-backdrop')) {
            if (!updateConfirmButton.disabled) {
                closeAddUpdateModal();
            }
        }
    });

    document.addEventListener('keydown', event => {
        if (modal.hidden && historyNotesModal.hidden && addUpdateModal.hidden) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            if (!modal.hidden && !confirmButton.disabled) closeModal();
            else if (!historyNotesModal.hidden) closeHistoryNotesModal();
            else if (!addUpdateModal.hidden && !updateConfirmButton.disabled) closeAddUpdateModal();
        }
    });

    notes?.addEventListener('input', () => {
        noteCounter.textContent = `${notes.value.length} / 300 characters`;
        if (notes.value.trim()) {
            noteError.hidden = true;
            notes.removeAttribute('aria-invalid');
        }
    });

    updateNotes?.addEventListener('input', () => {
        updateNoteCounter.textContent = `${updateNotes.value.length} / 300 characters`;
        if (updateNotes.value.trim()) {
            updateNoteError.hidden = true;
            updateNotes.removeAttribute('aria-invalid');
        }
    });

    const escapeHtml = str => String(str ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[ch]));

    const addHistoryEntry = data => {
        historyBody?.querySelector('[data-empty-history]')?.remove();
        const row = document.createElement('tr');
        const isUpdate = data.entry_type === 'update';
        const statusHtml = isUpdate
            ? '<span class="badge update-tag">Update</span>'
            : `<span class="badge ${statusClass(data.status)}">${escapeHtml(data.status)}</span>`;

        const [date, ...restTime] = (data.changed_at || '').split(',');
        const time = restTime.join(',').trim();
        const viewBtnHtml = data.notes
            ? `<button type="button" class="history-notes-view-btn" data-notes="${escapeHtml(data.notes)}" data-status="${escapeHtml(data.status)}" data-entry-type="${escapeHtml(data.entry_type || 'status_change')}" data-category="${escapeHtml(data.category || '')}" data-changed-by="${escapeHtml(data.dispatcher)}" data-date="${escapeHtml(date)}" data-time="${escapeHtml(time)}">View</button>`
            : '';

        row.innerHTML = `<td>${statusHtml}</td><td>${escapeHtml(data.team_name || '—')}</td><td>${escapeHtml(data.dispatcher)}</td><td>${escapeHtml(data.changed_at)}</td><td>${viewBtnHtml}</td>`;
        historyBody?.prepend(row);
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

        if (currentSeverityBadge && data.severity) {
            currentSeverityBadge.className = `badge ${severityClass(data.severity)}`;
            currentSeverityBadge.textContent = data.severity;
        }
        if (dispatchedTeamDisplay && data.team_name) {
            dispatchedTeamDisplay.textContent = data.team_name;
        }

        updateStepper(data.status);
        addHistoryEntry(data);

        const statusDropdown = document.getElementById('status-select-dropdown');
        if (statusDropdown) {
            const dispatchOpt = statusDropdown.querySelector('[data-dropdown-option][data-value="Dispatched"]');
            if (dispatchOpt) {
                dispatchOpt.textContent = data.status === 'Dispatched' ? 'Dispatched (Re-dispatch team)' : 'Dispatched';
            }
            const receivedOpt = statusDropdown.querySelector('[data-dropdown-option][data-value="Received"]');
            if (receivedOpt) {
                receivedOpt.textContent = data.status === 'Dispatched' ? 'Received (Cancel dispatch)' : 'Received';
            }
        }

        if (window.updateIncidentMapStatus && data.incident_id) {
            window.updateIncidentMapStatus(data.incident_id, data.status);
        }

        return currentStatusBadge;
    };

    const fireToast = (message, type = 'success') => {
        if (typeof window.showToast === 'function') {
            window.showToast({ type, message });
        }
    };

    form.addEventListener('submit', async event => {
        event.preventDefault();
        clearErrors();

        if (changedAtInput && changedDateInput?.value && changedTimeInput?.value) {
            changedAtInput.value = `${changedDateInput.value}T${changedTimeInput.value}`;
        }
        if (!changedAtInput?.value) {
            showFieldError(changedAtError, changedDateInput || changedAtInput, 'Date and time is required.');
            (changedDateInput || changedAtInput)?.focus();
            return;
        }
        if (statusInput.value === 'Dispatched' && teamSelect && !teamSelect.value && !externalTeamInput?.value?.trim()) {
            showFieldError(teamError, document.getElementById('status-modal-team-search') || teamSelect, 'Please select a response team or enter an external team name.');
            (document.getElementById('status-modal-team-search') || teamSelect).focus();
            return;
        }
        if (!notes.value.trim()) {
            showFieldError(noteError, notes, 'A note is required.');
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
                if (data.errors?.changed_at?.[0]) {
                    showFieldError(changedAtError, changedAtInput, data.errors.changed_at[0]);
                } else if (data.errors?.response_team_id?.[0]) {
                    showFieldError(teamError, teamSelect, data.errors.response_team_id[0]);
                } else if (data.errors?.notes?.[0]) {
                    showFieldError(noteError, notes, data.errors.notes[0]);
                } else {
                    showFieldError(modalError, null, data.errors?.status?.[0] || data.message || 'The status could not be updated.');
                }
                return;
            }

            const nextFocus = updatePage(data);
            closeModal(nextFocus);
            notes.value = '';
            noteCounter.textContent = '0 / 300 characters';
            window.dispatchEvent(new CustomEvent('resqlink:incident-status-updated'));
            fireToast(data.message);
        } catch {
            showFieldError(modalError, null, 'The status could not be updated. Check your connection and try again.');
        } finally {
            confirmButton.disabled = false;
            modal.removeAttribute('aria-busy');
        }
    });

    addUpdateForm?.addEventListener('submit', async event => {
        event.preventDefault();
        clearUpdateErrors();

        const categorySelect = addUpdateForm.querySelector('select[name="category"]');
        if (!categorySelect?.value.trim()) {
            showUpdateError('Please select a category.', false);
            categorySelect?.focus();
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
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(addUpdateForm),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const noteMessage = data.errors?.notes?.[0];
                const categoryMessage = data.errors?.category?.[0];
                showUpdateError(noteMessage || categoryMessage || data.message || 'The update could not be saved.', Boolean(noteMessage));
                return;
            }

            updatePage(data);
            closeAddUpdateModal();
            fireToast(data.message);
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
        openModal(statusSelect, statusInput.value || 'Received', true);
    }

    historyBody?.addEventListener('click', event => {
        const viewButton = event.target.closest('.history-notes-view-btn');
        if (viewButton) {
            openHistoryNotesModal(viewButton);
        }
    });
})();
