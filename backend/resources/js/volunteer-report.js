import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const DEFAULT_CENTER = [10.3157, 123.8854];

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-volunteer-map]').forEach((wrapper) => initializeVolunteerMap(wrapper));
});

async function initializeVolunteerMap(wrapper) {
    const map = L.map(wrapper.querySelector('[data-map-canvas]'), { zoomControl: true }).setView(DEFAULT_CENTER, 13);
    const latitude = document.querySelector('[name="latitude"]');
    const longitude = document.querySelector('[name="longitude"]');
    const radius = document.querySelector('[name="impact_radius"]');
    const slider = wrapper.querySelector('[data-radius-slider]');
    const radiusValue = wrapper.querySelector('[data-radius-value]');
    const noPin = wrapper.querySelector('[data-no-pin]');
    const submit = document.querySelector('[data-submit-incident]');
    let marker = null;
    let circle = null;

    const localLayers = await loadLocalMapData(map, wrapper);
    bindBarangaySearch(wrapper, map, localLayers.barangays);

    function updateRadius() {
        const value = Number(slider.value);
        radiusValue.textContent = value;
        radius.value = value;
        if (circle) circle.setRadius(value);
    }

    function placePin(position) {
        noPin.hidden = true;
        if (!marker) {
            marker = L.marker(position, { draggable: true }).addTo(map);
            marker.on('drag', () => placePin(marker.getLatLng()));
            circle = L.circle(position, { radius: Number(slider.value), className: 'volunteer-boundary-active', weight: 2 }).addTo(map);
        } else {
            marker.setLatLng(position);
            circle.setLatLng(position);
        }
        latitude.value = position.lat.toFixed(7);
        longitude.value = position.lng.toFixed(7);
        updateRadius();
        submit.disabled = false;
    }

    map.on('click', (event) => placePin(event.latlng));
    slider.addEventListener('input', updateRadius);
    updateRadius();
}

async function loadLocalMapData(map, wrapper) {
    const sources = {
        barangays: '/maps/cebu-city-barangays.geojson',
        roads: '/maps/cebu-city-osm-roads.geojson',
        landmarks: '/maps/cebu-city-osm-landmarks.geojson',
        facilities: '/maps/cebu-city-osm-emergency-facilities.geojson',
    };

    const data = {};
    const failedLayers = [];
    for (const [key, url] of Object.entries(sources)) {
        try {
            const response = await fetch(url, { headers: { Accept: 'application/geo+json' } });
            if (!response.ok) throw new Error(`${key} map data unavailable`);
            data[key] = await response.json();
        } catch (error) {
            failedLayers.push(key);
            console.error(`Unable to load volunteer ${key} map data.`, error);
        }
    }

    try {
        const barangays = data.barangays ? L.geoJSON(data.barangays, {
            style: { color: '#2563eb', fillColor: '#93c5fd', fillOpacity: 0.12, weight: 1.5 },
            onEachFeature: (feature, boundary) => {
                const properties = feature.properties || {};
                const name = properties.ADM4_EN || properties.psgc_name || 'Barangay';
                const code = properties.ADM4_PCODE || properties.psgc_code || '';
                boundary.bindTooltip(`${name}${code ? ` (${code})` : ''}`);
                boundary.on({ mouseover: (event) => event.target.setStyle({ color: '#c8262a', fillColor: '#fca5a5', fillOpacity: 0.25, weight: 2 }), mouseout: (event) => event.target.setStyle({ color: '#2563eb', fillColor: '#93c5fd', fillOpacity: 0.12, weight: 1.5 }) });
                boundary.feature.__searchName = name.toLowerCase();
            },
        }).addTo(map) : L.featureGroup().addTo(map);
        if (data.roads) L.geoJSON(data.roads, { style: { color: '#94a3b8', opacity: 0.55, weight: 1.2 } }).addTo(map);
        if (data.landmarks) L.geoJSON(data.landmarks, { pointToLayer: (feature, latlng) => L.circleMarker(latlng, { radius: 2.5, color: '#64748b', fillColor: '#f59e0b', fillOpacity: 0.8, weight: 1 }), onEachFeature: (feature, layer) => layer.bindTooltip(feature.properties?.name || 'Landmark') }).addTo(map);
        if (data.facilities) L.geoJSON(data.facilities, { pointToLayer: (feature, latlng) => L.marker(latlng), onEachFeature: (feature, layer) => layer.bindTooltip(feature.properties?.name || 'Emergency facility') }).addTo(map);
        const bounds = barangays.getBounds();
        if (bounds.isValid()) map.fitBounds(bounds.pad(0.08));
        if (failedLayers.length) showLayerWarning(wrapper, failedLayers);
        return { barangays };
    } catch (error) {
        const message = document.createElement('small');
        message.className = 'volunteer-map-error';
        message.textContent = 'Local volunteer map data could not be displayed.';
        wrapper.append(message);
        console.error('Unable to load local barangay map data.', error);
        return { barangays: L.featureGroup().addTo(map) };
    }
}

function showLayerWarning(wrapper, failedLayers) {
    const message = document.createElement('small');
    message.className = 'volunteer-map-error';
    message.textContent = `Some local map layers are unavailable: ${failedLayers.join(', ')}.`;
    wrapper.append(message);
}

function bindBarangaySearch(wrapper, map, layer) {
    const input = wrapper.querySelector('[data-barangay-search]');
    input.addEventListener('change', () => {
        const query = input.value.trim().toLowerCase();
        if (!query) return;
        const match = layer.getLayers().find((item) => item.feature?.__searchName?.includes(query));
        if (match?.getBounds) map.fitBounds(match.getBounds(), { maxZoom: 16 });
    });
}
