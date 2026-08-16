(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var mapEl = document.getElementById('incident-map');
        if (!mapEl) {
            return;
        }

        var statusColors = {
            Reported: '#DC2626',
            received: '#D97706',
            dispatched: '#16A34A',
        };

        var incidents = typeof incidentMapData !== 'undefined' ? incidentMapData : [];
        mapEl.innerHTML = '<div class="map-grid" aria-label="Offline incident coordinate grid"></div>';

        incidents.forEach(function (incident) {
            if (incident.latitude === null || incident.longitude === null) {
                return;
            }

            var marker = document.createElement('span');
            var latitude = Math.max(0, Math.min(100, (incident.latitude + 90) / 180 * 100));
            var longitude = Math.max(0, Math.min(100, (incident.longitude + 180) / 360 * 100));
            var color = statusColors[incident.status] || '#2563EB';

            marker.className = 'offline-map-marker';
            marker.style.backgroundColor = color;
            marker.style.left = longitude + '%';
            marker.style.bottom = latitude + '%';
            marker.title = (incident.category || 'Incident') + ' — ' + (incident.barangay || '');
            mapEl.appendChild(marker);
        });

        mapEl.insertAdjacentHTML('beforeend', '<small class="offline-map-label">Offline coordinate grid</small>');
    });
})();
