import L from 'leaflet';
import { computeBoundingBox, findEnclosingFeature } from './spatial-utils';

export async function addBarangayLayer(map) {
    try {
        const response = await fetch('/maps/cebu-city-barangays.geojson', {
            headers: { Accept: 'application/geo+json' },
        });
        if (!response.ok) throw new Error('Cebu City barangay boundaries unavailable');

        const geojsonData = await response.json();

        if (!map.getPane('barangaysPane')) {
            map.createPane('barangaysPane');
            map.getPane('barangaysPane').style.zIndex = '350';
        }

        const defaultStyle = {
            color: '#64748b',
            weight: 1.5,
            dashArray: '3 3',
            fillColor: '#3b82f6',
            fillOpacity: 0.04,
            pane: 'barangaysPane',
        };

        const hoverStyle = {
            color: '#2563eb',
            weight: 2.5,
            dashArray: undefined,
            fillColor: '#2563eb',
            fillOpacity: 0.18,
        };

        const indexedBarangays = [];

        const geoJsonLayer = L.geoJSON(geojsonData, {
            pane: 'barangaysPane',
            style: () => defaultStyle,
            onEachFeature: (feature, layer) => {
                const name = feature.properties?.ADM4_EN || feature.properties?.psgc_name || 'Unknown';
                const code = feature.properties?.ADM4_PCODE || feature.properties?.psgc_code || '';

                indexedBarangays.push({
                    name,
                    code,
                    feature,
                    layer,
                    bbox: computeBoundingBox(feature.geometry),
                });

                layer.bindTooltip(
                    `<strong>${escapeHtml(name)}</strong><br><span style="font-size:11px;color:#64748b;">Code: ${escapeHtml(code)}</span>`,
                    { sticky: true, className: 'resqlink-barangay-tooltip' }
                );

                layer.bindPopup(
                    `<div class="resqlink-popup-card">
                        <strong>Barangay ${escapeHtml(name)}</strong><br>
                        NAMRIA Code: <code>${escapeHtml(code)}</code><br>
                        PSGC: <code>${escapeHtml(feature.properties?.psgc_code || '—')}</code><br>
                        City: Cebu City
                    </div>`,
                    { className: 'resqlink-incident-popup' }
                );

                layer.on({
                    mouseover: () => {
                        layer.setStyle(hoverStyle);
                        layer.bringToFront();
                    },
                    mouseout: () => {
                        geoJsonLayer.resetStyle(layer);
                    },
                });
            },
        }).addTo(map);

        return {
            layer: geoJsonLayer,
            items: indexedBarangays,
            findBarangay(latlng) {
                const lat = Number(latlng.lat ?? latlng[0]);
                const lng = Number(latlng.lng ?? latlng[1]);
                return findEnclosingFeature(lat, lng, indexedBarangays);
            },
            search(query) {
                if (!query) return [];
                const normalized = query.trim().toLowerCase();
                return indexedBarangays
                    .map((item) => ({
                        ...item,
                        score: item.name.toLowerCase() === normalized ? 100 : item.name.toLowerCase().includes(normalized) ? 50 : 0,
                    }))
                    .filter((item) => item.score > 0)
                    .sort((first, second) => second.score - first.score);
            },
        };
    } catch {
        return {
            layer: null,
            items: [],
            findBarangay: () => null,
            search: () => [],
        };
    }
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[character]));
}
