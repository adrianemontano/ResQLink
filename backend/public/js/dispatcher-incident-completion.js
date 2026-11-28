(() => {
    const statusBadge = document.getElementById('incident-current-status');
    const actions = document.getElementById('incident-status-actions');
    const banner = document.getElementById('completed-incident-banner');
    const modal = document.getElementById('completed-incident-modal');
    if (!statusBadge || !modal) return;

    const dismiss = () => {
        modal.hidden = true;
        document.body.classList.remove('status-modal-open');
        statusBadge.focus();
    };

    const completeIncident = () => {
        if (statusBadge.textContent.trim() !== 'Completed') return;
        if (actions) actions.hidden = true;
        if (banner) banner.hidden = false;
        document.querySelectorAll('.history-notes-view-btn').forEach(button => {
            button.removeAttribute('data-update-url');
        });
        modal.hidden = false;
        document.body.classList.add('status-modal-open');
        modal.querySelector('button[data-completion-dismiss]')?.focus();
    };

    modal.addEventListener('click', event => {
        if (event.target.closest('[data-completion-dismiss]')) dismiss();
    });

    modal.addEventListener('keydown', event => {
        if (event.key === 'Escape') dismiss();
    });

    window.addEventListener('resqlink:incident-status-updated', completeIncident);
})();
