import {
    addLocalMapControls, createLocalMap, escapeHtml, featureBounds, featureName,
    loadGeoJson, loadLocalMapDatasets, maplibregl, radiusPolygon,
} from './map/local-map';

const STATUS_COLORS = {
    Reported: '#dc2626', Received: '#d97706', Dispatched: '#16a34a', Completed: '#64748b',
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-resqlink-map]').forEach(initializeMap);
});

async function initializeMap(wrapper) {
    const message = wrapper.querySelector('[data-map-message]');
    const map = createLocalMap(wrapper.querySelector('.resqlink-map'));
    addLocalMapControls(map, 'bottom-right');
    map.on('error', () => showMessage(message, 'The local map background is unavailable. Check the local tile server.', true));

    const datasetsPromise = loadLocalMapDatasets((name) => {
        showMessage(message, `${formatName(name)} map data is unavailable.`, true);
    });

    map.on('load', async () => {
        const datasets = await datasetsPromise;
        addReferenceLayers(map, datasets);
        bindSearch(map, wrapper, datasets);

        const incidents = await loadIncidents(wrapper, message);
        const visibleIncidents = selectIncidentFeatures(incidents, wrapper.dataset.focusIncidentId);
        addIncidentLayers(map, visibleIncidents);
        bindIncidentPopups(map);
        bindStatusFilters(map, wrapper);
        setInitialView(map, wrapper, visibleIncidents, datasets.barangays);
        bindMapInspection(map);
    });
}

function addReferenceLayers(map, datasets) {
    addGeoJsonLayer(map, 'dispatcher-roads', datasets.roads, 'line', {
        'line-color': '#64748b', 'line-opacity': 0.55, 'line-width': 1.2,
    });
    addGeoJsonLayer(map, 'dispatcher-barangay-fill', datasets.barangays, 'fill', {
        'fill-color': '#2563eb',
        'fill-opacity': ['case', ['boolean', ['feature-state', 'hover'], false], 0.12, 0],
    }, true);
    addGeoJsonLayer(map, 'dispatcher-barangay-outline', datasets.barangays, 'line', {
        'line-color': '#1d4ed8',
        'line-opacity': ['case', ['boolean', ['feature-state', 'hover'], false], 1, 0.25],
        'line-width': ['case', ['boolean', ['feature-state', 'hover'], false], 2.5, 1],
    }, true);
    addGeoJsonLayer(map, 'dispatcher-landmarks', datasets.landmarks, 'circle', {
        'circle-radius': 3, 'circle-color': '#f59e0b', 'circle-opacity': 0.65,
        'circle-stroke-color': '#64748b', 'circle-stroke-width': 1,
    });
    addGeoJsonLayer(map, 'dispatcher-facilities', datasets.facilities, 'circle', {
        'circle-radius': 7, 'circle-color': '#2563eb', 'circle-opacity': 0.78,
        'circle-stroke-color': '#fff', 'circle-stroke-width': 2,
    });
    bindBarangayHover(map);
}

function addGeoJsonLayer(map, id, data, type, paint, generateId = false) {
    if (!data) return;
    const sourceId = id.includes('barangay') ? 'dispatcher-barangays' : id;
    if (!map.getSource(sourceId)) map.addSource(sourceId, { type: 'geojson', data, generateId });
    map.addLayer({ id, source: sourceId, type, paint });
}

function bindBarangayHover(map) {
    let hoveredId = null;
    const layers = ['dispatcher-barangay-fill', 'dispatcher-barangay-outline'];
    const clear = () => {
        if (hoveredId !== null) map.setFeatureState({ source: 'dispatcher-barangays', id: hoveredId }, { hover: false });
        hoveredId = null;
        map.getCanvas().style.cursor = '';
    };
    layers.forEach((layer) => {
        map.on('mousemove', layer, (event) => {
            clear();
            hoveredId = event.features[0]?.id ?? null;
            if (hoveredId !== null) map.setFeatureState({ source: 'dispatcher-barangays', id: hoveredId }, { hover: true });
            map.getCanvas().style.cursor = 'pointer';
        });
        map.on('mouseleave', layer, clear);
    });
}

async function loadIncidents(wrapper, message) {
    try {
        const incidents = await loadGeoJson(wrapper.dataset.incidentsUrl);
        if (!incidents.features?.length) showMessage(message, 'No reported incidents have map coordinates yet.', false);
        return incidents;
    } catch (error) {
        showMessage(message, 'Incident locations could not be loaded.', true);
        console.error('Unable to load dispatcher incidents.', error);
        return { type: 'FeatureCollection', features: [] };
    }
}

function selectIncidentFeatures(collection, focusIncidentId) {
    if (!focusIncidentId) return collection;
    return {
        ...collection,
        features: collection.features.filter((feature) => String(feature.properties?.id) === String(focusIncidentId)),
    };
}

function addIncidentLayers(map, incidents) {
    const radiusFeatures = incidents.features
        .filter((feature) => Number(feature.properties?.impact_radius) > 0)
        .map((feature) => ({
            ...radiusPolygon({ lng: feature.geometry.coordinates[0], lat: feature.geometry.coordinates[1] }, Number(feature.properties.impact_radius)),
            properties: { status: feature.properties.status },
        }));

    map.addSource('dispatcher-impact-radii', {
        type: 'geojson', data: { type: 'FeatureCollection', features: radiusFeatures },
    });
    map.addLayer({
        id: 'dispatcher-impact-radii', source: 'dispatcher-impact-radii', type: 'fill',
        paint: { 'fill-color': statusColorExpression(), 'fill-opacity': 0.16 },
    });
    map.addSource('dispatcher-incidents', { type: 'geojson', data: incidents });
    map.addLayer({
        id: 'dispatcher-incidents', source: 'dispatcher-incidents', type: 'circle',
        paint: {
            'circle-radius': 9, 'circle-color': statusColorExpression(),
            'circle-stroke-color': '#fff', 'circle-stroke-width': 3,
        },
    });
}

function statusColorExpression() {
    return ['match', ['get', 'status'],
        'Reported', STATUS_COLORS.Reported,
        'Received', STATUS_COLORS.Received,
        'Dispatched', STATUS_COLORS.Dispatched,
        'Completed', STATUS_COLORS.Completed,
        '#2563eb'];
}

function bindIncidentPopups(map) {
    map.on('mouseenter', 'dispatcher-incidents', () => { map.getCanvas().style.cursor = 'pointer'; });
    map.on('mouseleave', 'dispatcher-incidents', () => { map.getCanvas().style.cursor = ''; });
    map.on('click', 'dispatcher-incidents', (event) => {
        event.originalEvent.cancelBubble = true;
        const feature = event.features[0];
        new maplibregl.Popup({ closeButton: true, className: 'resqlink-incident-popup' })
            .setLngLat(feature.geometry.coordinates)
            .setHTML(incidentPopup(feature.properties))
            .addTo(map);
    });
}

function incidentPopup(incident) {
    return `<div class="resqlink-popup-card">
        <div class="resqlink-popup-title">${escapeHtml(incident.reference)} (${escapeHtml(incident.status)})</div>
        <div class="resqlink-popup-reporter">Reporter: ${escapeHtml(incident.reporter)}</div>
        <div class="resqlink-popup-category">${escapeHtml(incident.category)} · ${escapeHtml(incident.barangay)}</div>
        <div class="resqlink-popup-members">Affected persons: ${escapeHtml(incident.affected_population)}</div>
        <div><a href="${escapeHtml(incident.detail_url)}">Open incident details</a></div>
    </div>`;
}

function bindStatusFilters(map, wrapper) {
    wrapper.querySelectorAll('[data-map-filter]').forEach((button) => {
        button.addEventListener('click', () => {
            const status = button.dataset.mapFilter;
            wrapper.querySelectorAll('[data-map-filter]').forEach((item) => item.classList.toggle('active', item === button));
            const filter = status === 'all' ? null : ['==', ['get', 'status'], status];
            map.setFilter('dispatcher-incidents', filter);
            map.setFilter('dispatcher-impact-radii', filter);
        });
    });
}

function bindSearch(map, wrapper, datasets) {
    const input = wrapper.querySelector('.map-search-box input');
    input?.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        const query = input.value.trim().toLowerCase();
        const features = [...(datasets.barangays?.features || []), ...(datasets.landmarks?.features || [])];
        const match = features.find((feature) => featureName(feature).toLowerCase() === query)
            || features.find((feature) => featureName(feature).toLowerCase().includes(query));
        const bounds = featureBounds(match);
        if (bounds) map.fitBounds(bounds, { padding: 45, maxZoom: 16 });
        else showMessage(wrapper.querySelector('[data-map-message]'), 'No matching barangay or landmark was found.', true);
    });
}

function setInitialView(map, wrapper, incidents, barangays) {
    const focusLat = Number(wrapper.dataset.focusLat);
    const focusLng = Number(wrapper.dataset.focusLng);
    if (Number.isFinite(focusLat) && Number.isFinite(focusLng)) {
        map.flyTo({ center: [focusLng, focusLat], zoom: 15 });
        return;
    }
    const coordinates = incidents.features.map((feature) => feature.geometry.coordinates);
    if (coordinates.length) {
        const bounds = coordinates.reduce((result, coordinate) => result.extend(coordinate), new maplibregl.LngLatBounds(coordinates[0], coordinates[0]));
        map.fitBounds(bounds, { padding: 60, maxZoom: 15 });
        return;
    }
    const bounds = featureBounds({
        geometry: { coordinates: barangays?.features?.map((feature) => feature.geometry.coordinates) || [] },
    });
    if (bounds) map.fitBounds(bounds, { padding: 35, maxZoom: 13 });
}

function bindMapInspection(map) {
    map.on('click', (event) => {
        if (map.queryRenderedFeatures(event.point, { layers: ['dispatcher-incidents'] }).length) return;
        const barangay = map.queryRenderedFeatures(event.point, { layers: ['dispatcher-barangay-fill'] })[0];
        const name = barangay ? featureName(barangay) : 'Outside mapped Cebu City barangays';
        new maplibregl.Popup()
            .setLngLat(event.lngLat)
            .setHTML(`<strong>${escapeHtml(name)}</strong><br>${event.lngLat.lat.toFixed(5)}, ${event.lngLat.lng.toFixed(5)}`)
            .addTo(map);
    });
}

function showMessage(element, text, isError) {
    if (!element) return;
    element.hidden = false;
    element.textContent = text;
    element.classList.toggle('error', isError);
}

function formatName(value) {
    return value.charAt(0).toUpperCase() + value.slice(1);
}
