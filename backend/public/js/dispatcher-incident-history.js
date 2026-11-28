(() => {
    const historyBody = document.querySelector('[data-status-history-body]');
    const modal = document.getElementById('history-notes-modal');
    const teamRow = document.getElementById('history-notes-team-row');
    const teamValue = document.getElementById('history-notes-team');
    const statusValue = document.getElementById('history-notes-status');
    if (!historyBody || !modal || !teamRow || !teamValue || !statusValue) return;

    const normalizeRows = () => {
        historyBody.querySelectorAll('tr').forEach(row => {
            if (row.cells.length !== 5) return;
            const teamCell = row.cells[1];
            const viewButton = row.querySelector('.history-notes-view-btn');
            if (viewButton && !viewButton.dataset.teamName) {
                viewButton.dataset.teamName = teamCell.textContent.trim();
            }
            teamCell.remove();
        });
    };

    normalizeRows();
    new MutationObserver(normalizeRows).observe(historyBody, { childList: true });

    historyBody.addEventListener('click', event => {
        const button = event.target.closest('.history-notes-view-btn');
        if (!button) return;

        const teamName = button.dataset.teamName || '';
        teamValue.textContent = teamName === '—' ? '' : teamName;
        teamRow.hidden = !teamValue.textContent;

        if (button.dataset.category === 'Resource cancelled') {
            statusValue.className = 'badge status-cancelled';
            statusValue.textContent = 'Cancelled Dispatch';
        }
    });
})();
