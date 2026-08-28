(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var mapEl = document.getElementById('incident-map') || document.getElementById('incident-mini-map');
        if (!mapEl) {
            return;
        }

        var statusColors = {
            Reported: '#DC2626',
            Received: '#D97706',
            Dispatched: '#16A34A',
            Completed: '#64748B',
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
            marker.addEventListener('click', function () {
                if (incident.detail_url) {
                    window.location.href = incident.detail_url;
                }
            });
            mapEl.appendChild(marker);

            var radius = document.createElement('span');
            var radiusSize = Math.max(18, Math.min(180, Number(incident.impact_radius || 0) / 100));
            radius.className = 'offline-map-radius';
            radius.style.borderColor = color;
            radius.style.height = radiusSize + 'px';
            radius.style.width = radiusSize + 'px';
            radius.style.left = 'calc(' + longitude + '% - ' + (radiusSize / 2) + 'px)';
            radius.style.bottom = 'calc(' + latitude + '% - ' + (radiusSize / 2) + 'px)';
            radius.title = 'Impact radius: ' + (incident.impact_radius || 0) + ' metres';
            mapEl.appendChild(radius);
        });

        mapEl.insertAdjacentHTML('beforeend', '<small class="offline-map-label">Offline coordinate grid</small>');
    });
})();
