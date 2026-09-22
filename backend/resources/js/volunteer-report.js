import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

const CENTER = [123.8854, 10.3157];
const STYLE_URL = window.RESQLINK_LOCAL_MAP_STYLE_URL || 'http://localhost:8080/styles/basic-preview/style.json';
const DATASETS = {
    barangays: '/maps/cebu-city-barangays.geojson',
    roads: '/maps/cebu-city-osm-roads.geojson',
    landmarks: '/maps/cebu-city-osm-landmarks.geojson',
    facilities: '/maps/cebu-city-osm-emergency-facilities.geojson',
};

document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('[data-volunteer-map]').forEach(initializeMap));

async function initializeMap(wrapper) {
    const map = new maplibregl.Map({
        container: wrapper.querySelector('[data-map-canvas]'),
        style: STYLE_URL,
        center: CENTER,
        zoom: 13,
        attributionControl: false,
    });
    map.addControl(new maplibregl.NavigationControl(), 'top-left');
    map.addControl(new maplibregl.AttributionControl({ customAttribution: window.RESQLINK_LOCAL_MAP_ATTRIBUTION }), 'bottom-right');

    const datasetsPromise = loadDatasets(wrapper);
    map.on('load', async () => {
        const datasets = await datasetsPromise;
        addOverlayLayers(map, datasets);
    });
    bindFormControls(map, wrapper);
}

async function loadDatasets(wrapper) {
    const datasets = {};
    for (const [name, url] of Object.entries(DATASETS)) {
        try {
            const response = await fetch(url, { headers: { Accept: 'application/geo+json' } });
            if (!response.ok) throw new Error(`${response.status} ${response.statusText}`);
            datasets[name] = await response.json();
        } catch (error) {
            showMapError(wrapper, `${name} overlay unavailable.`);
            console.error(`Unable to load volunteer ${name} overlay.`, error);
        }
    }
    return datasets;
}

function addOverlayLayers(map, datasets) {
    const hoverPopup = new maplibregl.Popup({ closeButton: false, closeOnClick: false, className: 'volunteer-hover-popup' });
    if (datasets.roads) addGeoJsonLayer(map, 'volunteer-roads', datasets.roads, { type: 'line', paint: { 'line-color': '#64748b', 'line-opacity': 0.55, 'line-width': 1.2 } });
    if (datasets.barangays) addBarangayLayers(map, datasets.barangays, hoverPopup);
    if (datasets.landmarks) addGeoJsonLayer(map, 'volunteer-landmarks', datasets.landmarks, { type: 'circle', paint: { 'circle-radius': 3, 'circle-color': '#f59e0b', 'circle-opacity': 0.55, 'circle-stroke-color': '#64748b', 'circle-stroke-opacity': 0.55, 'circle-stroke-width': 1 } }, true, hoverPopup);
    if (datasets.facilities) addGeoJsonLayer(map, 'volunteer-facilities', datasets.facilities, { type: 'circle', paint: { 'circle-radius': 7, 'circle-color': '#2563eb', 'circle-opacity': 0.7, 'circle-stroke-color': '#fff', 'circle-stroke-opacity': 0.7, 'circle-stroke-width': 2 } }, true, hoverPopup);
}

function addBarangayLayers(map, data, hoverPopup) {
    map.addSource('volunteer-barangays', { type: 'geojson', data, generateId: true });
    map.addLayer({ id: 'volunteer-barangay-fill', source: 'volunteer-barangays', type: 'fill', paint: { 'fill-color': '#2563eb', 'fill-opacity': ['case', ['boolean', ['feature-state', 'hover'], false], 0.12, 0] } });
    map.addLayer({ id: 'volunteer-barangay-outline', source: 'volunteer-barangays', type: 'line', paint: { 'line-color': '#1d4ed8', 'line-opacity': ['case', ['boolean', ['feature-state', 'hover'], false], 1, 0], 'line-width': ['case', ['boolean', ['feature-state', 'hover'], false], 2.5, 0] } });
    bindBarangayHover(map, hoverPopup);
}

function bindBarangayHover(map, hoverPopup) {
    let hoveredId = null;
    const enterBarangay = (event) => {
        map.getCanvas().style.cursor = 'pointer';
        hoveredId = event.features[0]?.id;
        if (hoveredId !== undefined) map.setFeatureState({ source: 'volunteer-barangays', id: hoveredId }, { hover: true });
        showFeaturePopup(map, event, event.features[0], 'Barangay', hoverPopup);
    };
    const moveBarangay = (event) => {
        if (event.features[0]) showFeaturePopup(map, event, event.features[0], 'Barangay', hoverPopup);
    };
    const leaveBarangay = () => {
        map.getCanvas().style.cursor = '';
        if (hoveredId !== null) map.setFeatureState({ source: 'volunteer-barangays', id: hoveredId }, { hover: false });
        hoveredId = null;
        hoverPopup.remove();
    };
    ['volunteer-barangay-fill', 'volunteer-barangay-outline'].forEach((layer) => {
        map.on('mouseenter', layer, enterBarangay);
        map.on('mousemove', layer, moveBarangay);
        map.on('mouseleave', layer, leaveBarangay);
    });
}

function addGeoJsonLayer(map, name, data, layer, interactive = false, hoverPopup = null) {
    map.addSource(name, { type: 'geojson', data, generateId: true });
    map.addLayer({ id: name, source: name, ...layer });
    if (interactive) {
        map.on('mouseenter', name, (event) => {
            map.getCanvas().style.cursor = 'pointer';
            showFeaturePopup(map, event, event.features[0], name === 'volunteer-facilities' ? 'Emergency facility' : 'Landmark', hoverPopup);
        });
        map.on('mouseleave', name, () => { map.getCanvas().style.cursor = ''; hoverPopup?.remove(); });
    }
}

function showFeaturePopup(map, event, feature, type, popup) {
    const properties = feature?.properties || {};
    const name = properties.ADM4_EN || properties.psgc_name || properties.name || properties.NAME || properties.fname || properties.amenity || type;
    popup
        .setLngLat(event.lngLat)
        .setHTML(`<strong>${escapeHtml(type)}</strong><br>${escapeHtml(name)}`)
        .addTo(map);
}

function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[character]));
}

function bindFormControls(map, wrapper) {
    const latitude = document.querySelector('[name="latitude"]');
    const longitude = document.querySelector('[name="longitude"]');
    const radius = document.querySelector('[name="impact_radius"]');
    const slider = wrapper.querySelector('[data-radius-slider]');
    const radiusValue = wrapper.querySelector('[data-radius-value]');
    const noPin = wrapper.querySelector('[data-no-pin]');
    const submit = document.querySelector('[data-submit-incident]');
    let marker;
    let circleSource;

    const updateRadius = () => {
        radiusValue.textContent = slider.value;
        radius.value = slider.value;
        if (circleSource) circleSource.setData(radiusPolygon(marker.getLngLat(), Number(slider.value)));
    };
    const placePin = (event) => {
        noPin.hidden = true;
        const position = event.lngLat;
        if (!marker) {
            marker = new maplibregl.Marker({ color: '#2563eb', draggable: true }).setLngLat(position).addTo(map);
            map.addSource('volunteer-impact-radius', { type: 'geojson', data: radiusPolygon(position, Number(slider.value)) });
            map.addLayer({ id: 'volunteer-impact-radius', source: 'volunteer-impact-radius', type: 'fill', paint: { 'fill-color': '#2563eb', 'fill-opacity': 0.16 } });
            circleSource = map.getSource('volunteer-impact-radius');
            marker.on('drag', () => syncPosition(marker.getLngLat()));
        } else marker.setLngLat(position);
        syncPosition(position);
    };
    const syncPosition = (position) => {
        latitude.value = Number(position.lat).toFixed(7);
        longitude.value = Number(position.lng).toFixed(7);
        submit.disabled = false;
    };
    map.on('click', placePin);
    slider.addEventListener('input', updateRadius);
    updateRadius();
}

function radiusPolygon(position, radius) {
    const points = [];
    const latitudeFactor = 111320;
    const longitudeFactor = 111320 * Math.cos((position.lat * Math.PI) / 180);
    for (let index = 0; index <= 64; index += 1) {
        const angle = (index / 64) * Math.PI * 2;
        points.push([position.lng + (Math.cos(angle) * radius) / longitudeFactor, position.lat + (Math.sin(angle) * radius) / latitudeFactor]);
    }
    return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [points] }, properties: {} };
}

function showMapError(wrapper, message) {
    const error = document.createElement('small');
    error.className = 'volunteer-map-error';
    error.textContent = message;
    wrapper.append(error);
}
