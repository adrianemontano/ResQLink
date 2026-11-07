(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var shell = document.querySelector('.dashboard-shell');
        if (!shell) {
            return;
        }

        /* --------------------------------------------------------------
           Desktop: collapse the side sidebar to a narrow strip.
           -------------------------------------------------------------- */
        var STORAGE_KEY = 'resqlink-sidebar-collapsed';
        var desktopToggle = document.querySelector('[data-sidebar-toggle]');
        var navLinks = shell.querySelectorAll('.sidebar-item');

        function setCollapsed(collapsed) {
            shell.classList.toggle('sidebar-collapsed', collapsed);

            if (desktopToggle) {
                desktopToggle.setAttribute('aria-expanded', String(!collapsed));
                desktopToggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            }

            navLinks.forEach(function (link) {
                if (collapsed) {
                    link.setAttribute('tabindex', '-1');
                    link.setAttribute('aria-disabled', 'true');
                } else {
                    link.removeAttribute('tabindex');
                    link.removeAttribute('aria-disabled');
                }
            });

            try {
                localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
            } catch (error) {
                // Storage unavailable; collapsed state simply won't persist.
            }
        }

        if (desktopToggle) {
            desktopToggle.addEventListener('click', function () {
                setCollapsed(!shell.classList.contains('sidebar-collapsed'));
            });
        }

        navLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                if (shell.classList.contains('sidebar-collapsed')) {
                    event.preventDefault();
                }
            });
        });

        var stored = null;
        try {
            stored = localStorage.getItem(STORAGE_KEY);
        } catch (error) {
            stored = null;
        }
        setCollapsed(stored === '1');

        /* --------------------------------------------------------------
           Mobile: dropdown drawer that slides down from the top.
           Opened/closed with the burger button, close button, backdrop,
           or by tapping a navigation link.
           -------------------------------------------------------------- */
        var openBtn = document.querySelector('[data-mobile-menu-toggle]');
        var closeBtn = document.querySelector('[data-sidebar-close]');
        var overlay = document.querySelector('[data-sidebar-overlay]');

        if (!openBtn) {
            return;
        }

        function setOpen(open) {
            shell.classList.toggle('sidebar-open', open);
            openBtn.setAttribute('aria-expanded', String(open));
            document.body.classList.toggle('menu-locked', open);
        }

        openBtn.addEventListener('click', function () {
            setOpen(!shell.classList.contains('sidebar-open'));
        });

        [closeBtn, overlay].forEach(function (el) {
            if (!el) {
                return;
            }
            el.addEventListener('click', function () {
                setOpen(false);
            });
        });

        shell.querySelectorAll('.sidebar-item').forEach(function (link) {
            link.addEventListener('click', function () {
                setOpen(false);
            });
        });
    });
})();
