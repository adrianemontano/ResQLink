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

    modal.addEventListener('click', event => {
        if (event.target.closest('[data-modal-dismiss]') && !confirmButton.disabled) {
            closeModal();
        }
    });

    document.addEventListener('keydown', event => {
        if (modal.hidden || event.key !== 'Escape') return;
        event.preventDefault();
        if (!confirmButton.disabled) closeModal();
    });

    notes?.addEventListener('input', () => {
        noteCounter.textContent = `${notes.value.length} / 300 characters`;
        if (notes.value.trim()) {
            noteError.hidden = true;
            notes.removeAttribute('aria-invalid');
        }
    });

    const updatePage = data => {
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
        window.dispatchEvent(new CustomEvent('resqlink:incident-history-entry', { detail: data }));

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

    noteCounter.textContent = `${notes.value.length} / 300 characters`;

    if (modal.dataset.openOnLoad === 'true') {
        openModal(statusSelect, statusInput.value || 'Received', true);
    }
})();
