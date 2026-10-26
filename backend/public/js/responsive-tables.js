(() => {
    const labelTables = (root) => {
        root.querySelectorAll('table').forEach((table) => {
            const headings = [...table.querySelectorAll('thead th')].map((heading) => heading.textContent.trim());
            if (!headings.length) return;

            table.querySelectorAll('tbody tr').forEach((row) => {
                [...row.cells].forEach((cell, index) => {
                    if (!cell.hasAttribute('data-label') && headings[index]) {
                        cell.dataset.label = headings[index];
                    }
                });
            });
        });
    };

    const initialize = () => {
        labelTables(document);
        new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) labelTables(node);
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
})();
