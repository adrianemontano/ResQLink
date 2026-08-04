(function () {
    var STORAGE_KEY = 'resqlink-sidebar-collapsed';

    document.addEventListener('DOMContentLoaded', function () {
        var shell = document.querySelector('.dashboard-shell');
        var toggle = document.querySelector('[data-sidebar-toggle]');

        if (!shell || !toggle) {
            return;
        }

        var navLinks = shell.querySelectorAll('.sidebar-item');

        function setCollapsed(collapsed) {
            shell.classList.toggle('sidebar-collapsed', collapsed);
            toggle.setAttribute('aria-expanded', String(!collapsed));
            toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');

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

        toggle.addEventListener('click', function () {
            setCollapsed(!shell.classList.contains('sidebar-collapsed'));
        });

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
    });
})();
