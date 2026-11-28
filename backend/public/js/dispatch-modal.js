// ResQLink Dispatch & Cancel Dispatch Modals + Confirmation System
(() => {
    // Shared Action Confirmation Modal
    window.showActionConfirm = function(title, message, onProceed) {
        const modal = document.getElementById('action-confirmation-modal');
        if (!modal) {
            if (window.confirm(message)) onProceed();
            return;
        }

        const titleEl = document.getElementById('action-confirm-title');
        const msgEl = document.getElementById('action-confirm-message');
        const okBtn = document.getElementById('action-confirm-proceed-btn');
        const cancelBtn = document.getElementById('action-confirm-cancel-btn');
        const backdrop = document.getElementById('action-confirm-backdrop');

        if (titleEl) titleEl.textContent = title;
        if (msgEl) msgEl.textContent = message;

        modal.hidden = false;
        document.body.classList.add('status-modal-open');

        const cleanup = () => {
            modal.hidden = true;
            document.body.classList.remove('status-modal-open');
            okBtn?.removeEventListener('click', handleOk);
            cancelBtn?.removeEventListener('click', handleCancel);
            backdrop?.removeEventListener('click', handleCancel);
        };

        const handleOk = () => {
            cleanup();
            onProceed();
        };

        const handleCancel = () => {
            cleanup();
        };

        okBtn?.addEventListener('click', handleOk);
        cancelBtn?.addEventListener('click', handleCancel);
        backdrop?.addEventListener('click', handleCancel);
    };

    // Global Modal Dismiss
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-modal-dismiss]') || (e.target.classList.contains('status-modal-backdrop') && !e.target.closest('#action-confirmation-modal'))) {
            const modals = ['dispatch-modal', 'cancel-dispatch-modal'];
            modals.forEach(id => {
                const el = document.getElementById(id);
                if (el) el.hidden = true;
            });
            document.body.classList.remove('status-modal-open');
        }
    });

    // Handle status dropdown menu item clicks for dispatch and cancel-dispatch
    document.addEventListener('click', (e) => {
        const item = e.target.closest('.status-dropdown-item');
        if (!item) return;

        // Block on completed incidents
        const currentStatusBadge = document.getElementById('incident-current-status');
        if (currentStatusBadge && currentStatusBadge.textContent?.trim() === 'Completed') {
            e.preventDefault();
            return;
        }

        const action = item.dataset.action;
        if (action === 'dispatch') {
            openDispatchModal();
        } else if (action === 'cancel-dispatch') {
            openCancelDispatchModal();
        }
    });

    function openDispatchModal() {
        const modal = document.getElementById('dispatch-modal');
        if (!modal) return;
        modal.hidden = false;
        document.body.classList.add('status-modal-open');
        document.getElementById('dispatch-team-search')?.focus();
    }

    function openCancelDispatchModal() {
        const modal = document.getElementById('cancel-dispatch-modal');
        if (!modal) return;
        modal.hidden = false;
        document.body.classList.add('status-modal-open');
    }

    // Dispatch Modal Logic
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('dispatch-modal');
        if (!modal) return;

        let allTeams = [];
        const selectedTeams = new Map();
        const externalTeams = [];

        const searchInput = document.getElementById('dispatch-team-search');
        const dropdownWrap = document.getElementById('dispatch-team-dropdown');
        const dropdownList = document.getElementById('dropdown-team-list');
        const selectedSection = document.getElementById('selected-teams-section');
        const selectedChips = document.getElementById('selected-teams-chips');
        const selectedCount = document.getElementById('selected-count');
        const hiddenInputs = document.getElementById('hidden-team-inputs');
        const notesInput = document.getElementById('dispatch-notes');
        const noteCounter = document.getElementById('dispatch-note-counter');
        const submitBtn = document.getElementById('btn-submit-dispatch');
        const form = document.getElementById('dispatch-form');

        const toggleExternalBtn = document.getElementById('btn-toggle-external');
        const externalRow = document.getElementById('external-team-row');
        const extNameInput = document.getElementById('external-name-input');
        const extAddBtn = document.getElementById('btn-add-external-confirm');

        async function loadTeams() {
            if (allTeams.length > 0) return;
            try {
                const res = await fetch('/dispatcher/response-teams', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    allTeams = await res.json();
                }
            } catch (err) {
                console.error('Failed to load teams', err);
            }
        }

        function renderDropdown(filterText = '') {
            const q = filterText.trim().toLowerCase();
            const matches = allTeams.filter(t => !q || t.name.toLowerCase().includes(q) || (t.type && t.type.toLowerCase().includes(q)));

            if (matches.length === 0) {
                dropdownList.innerHTML = '<div class="dropdown-team-empty">No matching teams found</div>';
                return;
            }

            dropdownList.innerHTML = matches.map(t => {
                const isSel = selectedTeams.has(t.id);
                return `
                    <div class="dropdown-team-item ${isSel ? 'is-selected' : ''}" data-id="${t.id}" data-name="${t.name}" data-type="${t.type || 'Unit'}">
                        <span>${t.name} ${isSel ? '✓' : ''}</span>
                        <span class="team-type-tag">${t.type || 'Unit'}</span>
                    </div>
                `;
            }).join('');
        }

        function renderSelected() {
            const total = selectedTeams.size + externalTeams.length;
            if (selectedCount) selectedCount.textContent = total;
            if (selectedSection) selectedSection.hidden = total === 0;

            let html = '';
            selectedTeams.forEach(t => {
                html += `
                    <div class="selected-team-row">
                        <span class="selected-team-name">${t.name}</span>
                        <div class="selected-team-meta">
                            <span class="team-type-tag">${t.type}</span>
                            <button type="button" class="btn-remove-selected-team" data-remove-id="${t.id}" title="Remove team">×</button>
                        </div>
                    </div>
                `;
            });

            externalTeams.forEach((ext, idx) => {
                html += `
                    <div class="selected-team-row">
                        <span class="selected-team-name">${ext}</span>
                        <div class="selected-team-meta">
                            <span class="team-type-tag" style="background: #FEF3C7; color: #92400E;">External</span>
                            <button type="button" class="btn-remove-selected-team" data-remove-ext="${idx}" title="Remove external team">×</button>
                        </div>
                    </div>
                `;
            });

            if (selectedChips) selectedChips.innerHTML = html;

            let hiddenHtml = '';
            selectedTeams.forEach(t => {
                hiddenHtml += `<input type="hidden" name="dispatch_team_ids[]" value="${t.id}">`;
            });
            externalTeams.forEach(ext => {
                hiddenHtml += `<input type="hidden" name="external_team_name[]" value="${ext}">`;
            });
            if (hiddenInputs) hiddenInputs.innerHTML = hiddenHtml;

            updateSubmitState();
        }

        function updateSubmitState() {
            const total = selectedTeams.size + externalTeams.length;
            const hasNotes = notesInput ? notesInput.value.trim().length > 0 : false;
            if (submitBtn) {
                submitBtn.disabled = total === 0 || !hasNotes;
            }
        }

        searchInput?.addEventListener('focus', async () => {
            await loadTeams();
            renderDropdown(searchInput.value);
            dropdownWrap.hidden = false;
        });

        searchInput?.addEventListener('input', () => {
            renderDropdown(searchInput.value);
            dropdownWrap.hidden = false;
        });

        document.addEventListener('click', e => {
            if (!searchInput?.contains(e.target) && !dropdownWrap?.contains(e.target)) {
                if (dropdownWrap) dropdownWrap.hidden = true;
            }
        });

        dropdownList?.addEventListener('click', e => {
            const item = e.target.closest('.dropdown-team-item');
            if (!item) return;
            const id = parseInt(item.dataset.id);
            const name = item.dataset.name;
            const type = item.dataset.type;

            if (selectedTeams.has(id)) {
                selectedTeams.delete(id);
            } else {
                selectedTeams.set(id, { id, name, type });
            }
            renderDropdown(searchInput.value);
            renderSelected();
        });

        selectedChips?.addEventListener('click', e => {
            const removeBtn = e.target.closest('.btn-remove-selected-team');
            if (!removeBtn) return;

            const doRemove = () => {
                if (removeBtn.dataset.removeId) {
                    selectedTeams.delete(parseInt(removeBtn.dataset.removeId));
                } else if (removeBtn.dataset.removeExt !== undefined) {
                    externalTeams.splice(parseInt(removeBtn.dataset.removeExt), 1);
                }
                renderDropdown(searchInput.value);
                renderSelected();
            };

            const isExternal = removeBtn.dataset.removeExt !== undefined;
            window.showActionConfirm(
                isExternal ? 'Remove external team' : 'Remove team',
                isExternal
                    ? 'Are you sure you want to remove this external team from the selection?'
                    : 'Are you sure you want to remove this team from the selection?',
                doRemove
            );
        });

        toggleExternalBtn?.addEventListener('click', () => {
            externalRow.hidden = !externalRow.hidden;
            if (!externalRow.hidden) extNameInput?.focus();
        });

        extAddBtn?.addEventListener('click', () => {
            const name = extNameInput.value.trim();
            if (name) {
                externalTeams.push(name);
                extNameInput.value = '';
                const contactInput = document.getElementById('external-contact-input');
                if (contactInput) contactInput.value = '';
                externalRow.hidden = true;
                renderSelected();
            }
        });

        notesInput?.addEventListener('input', () => {
            if (noteCounter) noteCounter.textContent = `${notesInput.value.length} / 300 characters`;
            updateSubmitState();
        });

        form?.addEventListener('submit', (e) => {
            const total = selectedTeams.size + externalTeams.length;
            if (total === 0) {
                e.preventDefault();
                return;
            }
            e.preventDefault();
            window.showActionConfirm(
                'Confirm Dispatch',
                `Are you sure you want to dispatch ${total} team(s) to this incident?`,
                () => {
                    form.submit();
                }
            );
        });
    });

    // Cancel Dispatch Modal Logic
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('cancel-dispatch-modal');
        if (!modal) return;

        const form = document.getElementById('cancel-dispatch-form');
        const submitBtn = document.getElementById('btn-submit-cancel');
        const notesInput = document.getElementById('cancel-notes');
        const noteCounter = document.getElementById('cancel-note-counter');
        const checkboxes = modal.querySelectorAll('.cancel-team-checkbox');

        function updateCancelSubmitState() {
            const checkedCount = [...checkboxes].filter(cb => cb.checked).length;
            const hasNotes = notesInput ? notesInput.value.trim().length > 0 : false;
            if (submitBtn) {
                submitBtn.disabled = checkedCount === 0 || !hasNotes;
                submitBtn.textContent = checkedCount > 0 ? `Confirm Cancellation (${checkedCount})` : 'Confirm Cancellation';
            }
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCancelSubmitState);
        });

        notesInput?.addEventListener('input', () => {
            if (noteCounter) noteCounter.textContent = `${notesInput.value.length} / 300 characters`;
            updateCancelSubmitState();
        });

        form?.addEventListener('submit', (e) => {
            const checkedCount = [...checkboxes].filter(cb => cb.checked).length;
            if (checkedCount === 0) {
                e.preventDefault();
                return;
            }
            e.preventDefault();
            window.showActionConfirm(
                'Confirm Cancellation',
                `Are you sure you want to cancel the dispatch for ${checkedCount} selected team(s)?`,
                () => {
                    form.submit();
                }
            );
        });
    });
})();
