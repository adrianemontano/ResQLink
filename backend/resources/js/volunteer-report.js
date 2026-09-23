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
    map.addControl(new maplibregl.NavigationControl(), 'top-right');
    map.addControl(new maplibregl.AttributionControl({ customAttribution: window.RESQLINK_LOCAL_MAP_ATTRIBUTION }), 'bottom-right');

    const datasetsPromise = loadDatasets(wrapper);
    map.on('load', async () => {
        const datasets = await datasetsPromise;
        addOverlayLayers(map, datasets);
        bindBarangaySearch(map, wrapper, datasets.barangays);
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
    addSelectedLandmarkLayer(map);
    if (datasets.facilities) addGeoJsonLayer(map, 'volunteer-facilities', datasets.facilities, { type: 'circle', paint: { 'circle-radius': 7, 'circle-color': '#2563eb', 'circle-opacity': 0.7, 'circle-stroke-color': '#fff', 'circle-stroke-opacity': 0.7, 'circle-stroke-width': 2 } }, true, hoverPopup);
}

function addSelectedLandmarkLayer(map) {
    map.addSource('volunteer-selected-landmark', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
    map.addLayer({ id: 'volunteer-selected-landmark', source: 'volunteer-selected-landmark', type: 'circle', paint: { 'circle-radius': 8, 'circle-color': '#dc2626', 'circle-opacity': 0.95, 'circle-stroke-color': '#fff', 'circle-stroke-width': 2 } });
}

function addBarangayLayers(map, data, hoverPopup) {
    map.__resqlinkBarangayFeatures = data.features || [];
    map.addSource('volunteer-barangays', { type: 'geojson', data, generateId: true });
    map.addLayer({ id: 'volunteer-barangay-fill', source: 'volunteer-barangays', type: 'fill', paint: { 'fill-color': '#2563eb', 'fill-opacity': ['case', ['boolean', ['feature-state', 'hover'], false], 0.12, 0] } });
    map.addLayer({ id: 'volunteer-barangay-outline', source: 'volunteer-barangays', type: 'line', paint: { 'line-color': '#1d4ed8', 'line-opacity': ['case', ['boolean', ['feature-state', 'hover'], false], 1, 0], 'line-width': ['case', ['boolean', ['feature-state', 'hover'], false], 2.5, 0] } });
    bindBarangayHover(map, hoverPopup);
}

function bindBarangaySearch(map, wrapper, data) {
    if (!data?.features?.length) return;
    const mapSearch = wrapper.querySelector('[data-barangay-search]');
    const formField = document.querySelector('[data-barangay-field]');
    const datalist = document.querySelector('#barangay-options');
    const features = data.features.map((feature) => ({ feature, name: barangayName(feature) }));
    features.forEach(({ name }) => {
        if (!datalist || [...datalist.options].some((option) => option.value === name)) return;
        const option = document.createElement('option');
        option.value = name;
        datalist.append(option);
    });
    const select = (value) => {
        const match = features.find(({ name }) => name.toLowerCase() === value.trim().toLowerCase());
        if (!match) return;
        const bounds = featureBounds(match.feature);
        if (bounds) map.fitBounds(bounds, { padding: 35, maxZoom: 15, animate: true });
        highlightBarangay(map, match.feature);
        if (formField) formField.value = match.name;
        if (mapSearch) mapSearch.value = match.name;
    };
    mapSearch?.addEventListener('change', () => select(mapSearch.value));
    mapSearch?.addEventListener('keydown', (event) => { if (event.key === 'Enter') select(mapSearch.value); });
    formField?.addEventListener('change', () => select(formField.value));
}

function barangayName(feature) {
    const properties = feature.properties || {};
    return properties.ADM4_EN || properties.psgc_name || properties.name || properties.NAME || '';
}

function featureBounds(feature) {
    const coordinates = [];
    const visit = (value) => Array.isArray(value[0]) ? value.forEach(visit) : coordinates.push(value);
    visit(feature.geometry?.coordinates || []);
    if (!coordinates.length) return null;
    return coordinates.reduce((bounds, coordinate) => bounds.extend(coordinate), new maplibregl.LngLatBounds(coordinates[0], coordinates[0]));
}

function highlightBarangay(map, feature) {
    const source = map.getSource('volunteer-selected-barangay');
    const data = { type: 'FeatureCollection', features: [feature] };
    if (source) source.setData(data);
    else {
        map.addSource('volunteer-selected-barangay', { type: 'geojson', data });
        map.addLayer({ id: 'volunteer-selected-barangay', source: 'volunteer-selected-barangay', type: 'line', paint: { 'line-color': '#dc2626', 'line-width': 3, 'line-opacity': 0.9 } });
    }
    window.clearTimeout(map.__barangayHighlightTimer);
    map.__barangayHighlightTimer = window.setTimeout(() => map.getSource('volunteer-selected-barangay')?.setData({ type: 'FeatureCollection', features: [] }), 1800);
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
    if (name === 'volunteer-landmarks') map.__resqlinkLandmarkFeatures = data.features || [];
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
    const barangayField = document.querySelector('[data-barangay-field]');
    const landmarkField = document.querySelector('[data-landmark-field]');
    let marker;
    let circleSource;
    let radiusVisible = false;

    const updateRadius = () => {
        radiusValue.textContent = slider.value;
        radius.value = slider.value;
        if (circleSource && radiusVisible) circleSource.setData(radiusPolygon(marker.getLngLat(), Number(slider.value)));
    };
    const placePin = (event) => {
        noPin.hidden = true;
        const position = event.lngLat;
        if (!marker) {
            marker = new maplibregl.Marker({ color: '#2563eb', draggable: true }).setLngLat(position).addTo(map);
            map.addSource('volunteer-impact-radius', { type: 'geojson', data: radiusPolygon(position, Number(slider.value)) });
            map.addLayer({ id: 'volunteer-impact-radius', source: 'volunteer-impact-radius', type: 'fill', paint: { 'fill-color': '#2563eb', 'fill-opacity': 0.16 } });
            circleSource = map.getSource('volunteer-impact-radius');
            marker.on('dragstart', () => { radiusVisible = false; circleSource.setData({ type: 'FeatureCollection', features: [] }); });
            marker.on('drag', () => syncPosition(marker.getLngLat()));
            marker.on('dragend', () => { radiusVisible = true; updateRadius(); });
        } else marker.setLngLat(position);
        syncPosition(position);
        radiusVisible = true;
        updateRadius();
    };
    const syncPosition = (position) => {
        latitude.value = Number(position.lat).toFixed(7);
        longitude.value = Number(position.lng).toFixed(7);
        const barangay = findBarangayAtPosition(map, position);
        if (barangay && barangayField) barangayField.value = barangayName(barangay);
        updateLandmarkChoices(map, position, barangay, landmarkField);
        submit.disabled = false;
    };
    map.on('click', placePin);
    slider.addEventListener('input', updateRadius);
    updateRadius();
}

function updateLandmarkChoices(map, position, barangay, landmarkField) {
    if (!landmarkField) return;
    const datalist = document.querySelector('#landmark-options');
    if (!datalist) return;
    if (!barangay) {
        datalist.replaceChildren();
        landmarkField.value = '';
        map.getSource('volunteer-selected-landmark')?.setData({ type: 'FeatureCollection', features: [] });
        return;
    }
    const landmarks = (map.__resqlinkLandmarkFeatures || [])
        .filter((feature) => pointInGeometry(feature.geometry?.coordinates, barangay.geometry))
        .map((feature) => ({ feature, name: landmarkName(feature) }))
        .filter(({ name }) => name);
    datalist.replaceChildren(...landmarks.map(({ name }) => {
        const option = document.createElement('option');
        option.value = name;
        return option;
    }));
    const nearest = landmarks
        .map((landmark) => ({ ...landmark, distance: distanceBetween(position, landmark.feature.geometry.coordinates) }))
        .sort((first, second) => first.distance - second.distance)[0];
    landmarkField.value = nearest?.name || '';
    map.getSource('volunteer-selected-landmark')?.setData({
        type: 'FeatureCollection',
        features: nearest ? [nearest.feature] : [],
    });
}

function landmarkName(feature) {
    const properties = feature.properties || {};
    return properties.name || properties.NAME || properties.amenity || properties.shop || '';
}

function distanceBetween(first, second) {
    return Math.hypot(first.lng - second[0], first.lat - second[1]);
}

function findBarangayAtPosition(map, position) {
    const features = map.__resqlinkBarangayFeatures || [];
    return features.find((feature) => pointInGeometry([position.lng, position.lat], feature.geometry));
}

function pointInGeometry(point, geometry) {
    if (!geometry) return false;
    if (geometry.type === 'Polygon') return pointInPolygon(point, geometry.coordinates[0]);
    return geometry.coordinates?.some((polygon) => pointInPolygon(point, polygon[0])) || false;
}

function pointInPolygon([x, y], polygon) {
    let inside = false;
    for (let index = 0, previous = polygon.length - 1; index < polygon.length; previous = index++) {
        const [xi, yi] = polygon[index];
        const [xj, yj] = polygon[previous];
        const intersects = ((yi > y) !== (yj > y)) && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi;
        if (intersects) inside = !inside;
    }
    return inside;
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
