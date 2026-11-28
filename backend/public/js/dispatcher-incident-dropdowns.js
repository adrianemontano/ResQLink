(() => {
    const toggle = document.getElementById('update-status-toggle');
    const menu = document.getElementById('status-dropdown-menu');
    const statusBadge = document.getElementById('incident-current-status');
    if (!toggle || !menu) return;
    let restoreToggleFocus = false;

    const syncVisibility = () => {
        const isOpen = menu.classList.contains('visible');
        menu.hidden = !isOpen;
        toggle.setAttribute('aria-expanded', String(isOpen));
    };

    toggle.addEventListener('click', syncVisibility);
    menu.addEventListener('click', event => {
        if (!event.target.closest('.status-dropdown-item')) return;
        restoreToggleFocus = true;
        menu.classList.remove('visible');
        syncVisibility();
    });

    const restoreFocus = () => {
        if (!restoreToggleFocus) return;
        restoreToggleFocus = false;
        if (statusBadge?.textContent.trim() !== 'Completed') toggle.focus();
    };

    document.addEventListener('click', event => {
        if (!menu.classList.contains('visible')) syncVisibility();
        if (event.target.closest('[data-modal-dismiss]')) requestAnimationFrame(restoreFocus);
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') requestAnimationFrame(restoreFocus);
    });

    window.addEventListener('resqlink:incident-status-updated', restoreFocus);
})();
