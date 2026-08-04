(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var mapEl = document.getElementById('incident-map');
        if (!mapEl || typeof L === 'undefined') {
            return;
        }

        var map = L.map(mapEl).setView([10.3157, 123.8854], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        var statusColors = {
            pending: '#DC2626',
            received: '#D97706',
            dispatched: '#16A34A',
        };

        var incidents = typeof incidentMapData !== 'undefined' ? incidentMapData : [];

        incidents.forEach(function (incident) {
            if (!incident.latitude || !incident.longitude) {
                return;
            }

            var color = statusColors[incident.status] || '#2563EB';

            L.circleMarker([incident.latitude, incident.longitude], {
                radius: 8,
                color: color,
                weight: 2,
                fillColor: color,
                fillOpacity: 0.85,
            })
                .addTo(map)
                .bindPopup('<strong>' + (incident.category || 'Incident') + '</strong><br>' + (incident.barangay || ''));
        });
    });
})();
