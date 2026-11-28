(() => {
    const historyBody = document.querySelector('[data-status-history-body]');
    const historyNotesModal = document.getElementById('history-notes-modal');
    if (!historyBody || !historyNotesModal) return;

    const historyNotesEditInput = document.getElementById('history-notes-edit-input');
    const historyNotesEditError = document.getElementById('history-notes-edit-error');
    const historyNotesEditBtn = document.getElementById('history-notes-edit-btn');
    const historyNotesSaveBtn = document.getElementById('history-notes-save-btn');
    const historyNotesCancelBtn = document.getElementById('history-notes-cancel-btn');
    const historyNotesCloseBtn = document.getElementById('history-notes-close-btn');
    const statusClass = status => `status-${String(status || 'reported').toLowerCase()}`;
    let notesOpener = null;

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
        } else if (button.dataset.category === 'Resource cancelled') {
            statusBadge.className = 'badge status-cancelled';
            statusBadge.textContent = 'Cancelled Dispatch';
            categorySpan.hidden = true;
            categorySpan.textContent = '';
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

    const escapeHtml = str => String(str ?? '').replace(/[&<>"']/g, ch => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[ch]));

    const addHistoryEntry = data => {
        historyBody?.querySelector('[data-empty-history]')?.remove();
        const row = document.createElement('tr');
        const isUpdate = data.entry_type === 'update';
        const isCancelledDispatch = data.category === 'Resource cancelled';
        const statusHtml = isUpdate
            ? '<span class="badge update-tag">Update</span>'
            : isCancelledDispatch
                ? '<span class="badge status-cancelled">Cancelled Dispatch</span>'
                : `<span class="badge ${statusClass(data.status)}">${escapeHtml(data.status)}</span>`;

        const [date, ...restTime] = (data.changed_at || '').split(',');
        const time = restTime.join(',').trim();
        const viewBtnHtml = data.notes
            ? `<button type="button" class="history-notes-view-btn" data-notes="${escapeHtml(data.notes)}" data-status="${escapeHtml(data.status)}" data-entry-type="${escapeHtml(data.entry_type || 'status_change')}" data-category="${escapeHtml(data.category || '')}" data-changed-by="${escapeHtml(data.dispatcher)}" data-date="${escapeHtml(date)}" data-time="${escapeHtml(time)}">View</button>`
            : '';

        row.innerHTML = `<td>${statusHtml}</td><td>${escapeHtml(data.team_name || '—')}</td><td>${escapeHtml(data.dispatcher)}</td><td>${escapeHtml(data.changed_at)}</td><td>${viewBtnHtml}</td>`;
        historyBody?.prepend(row);
    };

    historyBody.addEventListener('click', event => {
        const viewButton = event.target.closest('.history-notes-view-btn');
        if (viewButton) openHistoryNotesModal(viewButton);
    });

    window.addEventListener('resqlink:incident-history-entry', event => {
        addHistoryEntry(event.detail);
    });
})();
