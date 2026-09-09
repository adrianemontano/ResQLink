import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const PHILIPPINES = {
    center: [12.8797, 121.7740],
    bounds: L.latLngBounds([4.5, 116.0], [21.5, 127.5]),
    initialZoom: 6,
};

const localFeatureLabels = {
    boundary: 'Service area',
    road: 'Road',
    landmark: 'Landmark',
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-resqlink-map]').forEach((wrapper) => initializeMap(wrapper));
});

async function initializeMap(wrapper) {
    const mapElement = wrapper.querySelector('.resqlink-map');
    const messageElement = wrapper.querySelector('[data-map-message]');
    const searchInput = wrapper.querySelector('.map-search-box input');
    const map = L.map(mapElement, {
        maxBounds: PHILIPPINES.bounds,
        maxBoundsViscosity: 0.75,
        minZoom: 5,
        maxZoom: 18,
        zoomControl: false,
        attributionControl: false,
        scrollWheelZoom: true,
        doubleClickZoom: true,
        zoomSnap: 0.25,
        worldCopyJump: false,
        preferCanvas: true,
    }).setView(PHILIPPINES.center, PHILIPPINES.initialZoom);

    map.__resqlinkStatus = 'all';
    map.__resqlinkMarkers = [];
    map.__resqlinkLocalFeatures = [];

    applyBaseLayer(map);
    bindFilterChips(map, wrapper);

    L.control.zoom({ position: 'bottomright' }).addTo(map);
    L.control.scale({ metric: true, imperial: false, position: 'bottomleft' }).addTo(map);

    const localFeatures = await addLocalFeatures(map);
    map.__resqlinkLocalFeatures = localFeatures;
    bindMapSearch(map, searchInput);
    const markers = await loadLocalIncidents(map, messageElement);
    map.__resqlinkMarkers = markers;
    applyMarkerFilter(map, map.__resqlinkStatus);
    const fitTargets = markers.length > 0 ? markers : localFeatures.map((feature) => feature.layer).filter(Boolean);

    if (fitTargets.length > 0) {
        const bounds = L.featureGroup(fitTargets).getBounds();
        if (bounds.isValid()) {
            map.fitBounds(bounds.pad(0.18), { maxZoom: 15, animate: true });
        }
    }

    map.on('click', (event) => showLocationPopup(map, event.latlng, localFeatures));
}

function applyBaseLayer(map) {
    map.getContainer().classList.add('map-grid');
}

function bindMapSearch(map, searchInput) {
    if (!searchInput) return;

    searchInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;

        const query = searchInput.value.trim();
        if (!query) return;

        const normalized = query.toLowerCase();
        const match = map.__resqlinkLocalFeatures
            .map((feature) => ({
                ...feature,
                score: feature.name.toLowerCase() === normalized ? 100 : feature.name.toLowerCase().includes(normalized) ? 50 : 0,
            }))
            .filter((feature) => feature.score > 0)
            .sort((first, second) => second.score - first.score)[0];

        if (!match) {
            showMessage(searchInput.closest('[data-resqlink-map]')?.querySelector('[data-map-message]'), 'No matching local place was found.', true);
            return;
        }

        const bounds = typeof match.layer.getBounds === 'function'
            ? match.layer.getBounds()
            : L.latLngBounds([match.layer.getLatLng()]);
        map.fitBounds(bounds.pad(0.35), { maxZoom: 16, animate: true });
        match.layer.openPopup();
    });
}

function bindFilterChips(map, wrapper) {
    const chips = wrapper.querySelectorAll('[data-map-filter]');
    if (!chips.length) return;

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            const selectedFilter = chip.dataset.mapFilter || 'all';
            map.__resqlinkStatus = selectedFilter;

            chips.forEach((button) => button.classList.toggle('active', button === chip));
            applyMarkerFilter(map, selectedFilter);
        });
    });
}

function applyMarkerFilter(map, filter) {
    if (!Array.isArray(map.__resqlinkMarkers)) return;

    const normalizedFilter = String(filter || 'all').toLowerCase();
    map.__resqlinkMarkers.forEach((marker) => {
        const status = String(marker.__resqlinkStatus || 'Reported').toLowerCase();
        const shouldShow = normalizedFilter === 'all' || status === normalizedFilter;
        const radius = marker.__resqlinkRadius;

        if (shouldShow) {
            marker.addTo(map);
            radius?.addTo(map);
        } else {
            map.removeLayer(marker);
            if (radius) map.removeLayer(radius);
        }
    });
}

async function addLocalFeatures(map) {
    try {
        const [contextData, boundaryData, hazardData] = await Promise.all([
            fetchLocalGeoJson('/maps/resqlink-map.geojson'),
            fetchLocalGeoJson('/maps/boundaries.geojson'),
            fetchLocalGeoJson('/maps/hazards.geojson'),
        ]);
        const localFeatures = [];

        const roads = L.geoJSON(contextData, {
            filter: (feature) => feature.properties?.kind === 'road',
            style: { color: '#E6EEF5', opacity: 0.95, weight: 7 },
            onEachFeature: (feature, layer) => bindFeaturePopup(layer, feature, 'Road'),
        }).addTo(map);
        localFeatures.push(...collectLocalFeatures(roads, 'road'));

        const boundaries = L.geoJSON(boundaryData, {
            style: (feature) => ({
                color: feature.properties?.color || '#7C3AED',
                dashArray: feature.properties?.dashed ? '7 7' : undefined,
                fill: false,
                opacity: 0.72,
                weight: 2,
            }),
            onEachFeature: (feature, layer) => bindFeaturePopup(layer, feature, 'Boundary'),
        }).addTo(map);
        localFeatures.push(...collectLocalFeatures(boundaries, 'boundary'));

        const hazards = L.geoJSON(hazardData, {
            pointToLayer: (feature, latlng) => L.marker(latlng, { icon: buildHazardIcon(), keyboard: false }),
            onEachFeature: (feature, layer) => bindFeaturePopup(layer, feature, 'Hazard'),
        }).addTo(map);
        localFeatures.push(...collectLocalFeatures(hazards, 'hazard'));

        const landmarks = L.geoJSON(contextData, {
            filter: (feature) => feature.properties?.kind === 'landmark',
            pointToLayer: (feature, latlng) => L.circleMarker(latlng, {
                color: '#475569',
                fillColor: '#F59E0B',
                fillOpacity: 1,
                radius: 7,
                weight: 2,
            }),
            onEachFeature: (feature, layer) => bindFeaturePopup(layer, feature, 'Landmark'),
        }).addTo(map);
        localFeatures.push(...collectLocalFeatures(landmarks, 'landmark'));

        return localFeatures;
    } catch {
        map.getContainer().classList.add('map-grid');
        return [];
    }
}

async function fetchLocalGeoJson(path) {
    const response = await fetch(path, { headers: { Accept: 'application/geo+json' } });
    if (!response.ok) throw new Error(`Local map data unavailable: ${path}`);
    return response.json();
}

function collectLocalFeatures(layerGroup, kind) {
    return layerGroup.getLayers()
        .filter((layer) => layer.feature?.properties?.name || layer.feature?.properties?.boundaryName)
        .map((layer) => ({
            name: layer.feature.properties.name || layer.feature.properties.boundaryName,
            kind,
            layer,
        }));
}

function showLocationPopup(map, latlng, localFeatures) {
    const nearby = localFeatures
        .map((feature) => ({
            ...feature,
            distance: getFeatureDistance(latlng, feature.layer),
        }))
        .filter((feature) => Number.isFinite(feature.distance))
        .sort((first, second) => first.distance - second.distance);

    const nearest = nearby[0];
    const matchingText = nearby.length > 0
        ? nearby.slice(0, 3).map(({ name, kind }) => `${localFeatureLabels[kind] || kind}: ${escapeHtml(name)}`).join('<br>')
        : 'No named local feature at this location.';

    const nearestLabel = nearest
        ? `<br>Nearest local feature: <strong>${escapeHtml(nearest.name)}</strong> (${localFeatureLabels[nearest.kind] || nearest.kind})`
        : '';

    map.openPopup(
        `<strong>Selected location</strong><br>Latitude: ${latlng.lat.toFixed(6)}<br>Longitude: ${latlng.lng.toFixed(6)}${nearestLabel}<br><br>${matchingText}`,
        latlng,
    );
}

function getFeatureDistance(latlng, layer) {
    if (!layer) return Number.POSITIVE_INFINITY;

    if (typeof layer.getLatLng === 'function') {
        return latlng.distanceTo(layer.getLatLng());
    }

    if (typeof layer.getBounds === 'function') {
        const bounds = layer.getBounds();
        if (bounds && bounds.isValid()) {
            return latlng.distanceTo(bounds.getCenter());
        }
    }

    return Number.POSITIVE_INFINITY;
}

function bindFeaturePopup(layer, feature, type) {
    const name = feature.properties?.name || feature.properties?.boundaryName || type;
    layer.bindPopup(`<strong>${escapeHtml(name)}</strong><br>${type} in the local map dataset.`);
}

async function loadLocalIncidents(map, messageElement) {
    try {
        const data = await fetchLocalGeoJson('/maps/incidents.geojson');
        const points = data.features.map((feature) => ({
            ...feature.properties,
            latitude: feature.geometry.coordinates[1],
            longitude: feature.geometry.coordinates[0],
        }));
        if (points.length === 0) showMessage(messageElement, 'No local incidents are available yet.', false);
        return points.flatMap((point) => renderMarker(map, point));
    } catch {
        showMessage(messageElement, 'Local incident data is temporarily unavailable.', true);
        return [];
    }
}

function renderMarker(map, point) {
    if (!Number.isFinite(Number(point.latitude)) || !Number.isFinite(Number(point.longitude))) return [];

    const status = normalizeStatus(point.status);
    const coordinates = [Number(point.latitude), Number(point.longitude)];
    const marker = L.marker(coordinates, {
        icon: buildIncidentIcon(),
        keyboard: false,
    }).addTo(map);
    marker.__resqlinkStatus = status;
    marker.bindPopup(buildIncidentPopup(point, status), { className: 'resqlink-incident-popup', closeButton: false, closeOnClick: true, autoPan: true });
    marker.on('popupopen', () => {
        marker.getPopup()?.getElement()?.querySelector('[data-popup-close]')?.addEventListener('click', () => map.closePopup());
    });

    if (Number(point.impact_radius) > 0) {
        marker.__resqlinkRadius = L.circle(coordinates, {
            color: '#22C55E',
            fillColor: '#22C55E',
            fillOpacity: 0.15,
            radius: Number(point.impact_radius),
            weight: 2,
        }).addTo(map);
    }

    return [marker];
}

function normalizeStatus(status) {
    const normalizedStatus = String(status || 'Reported').trim().toLowerCase();

    return normalizedStatus.charAt(0).toUpperCase() + normalizedStatus.slice(1);
}

function buildIncidentIcon() {
    return L.divIcon({
        className: 'resqlink-incident-icon',
        html: '<span></span>',
        iconSize: [18, 18],
        iconAnchor: [9, 9],
        popupAnchor: [0, -10],
    });
}

function buildHazardIcon() {
    return L.divIcon({
        className: 'resqlink-hazard-icon',
        html: '<span></span>',
        iconSize: [18, 18],
        iconAnchor: [9, 9],
    });
}

function buildIncidentPopup(point, status) {
    return `<div class="resqlink-popup-card">
        <button type="button" class="resqlink-popup-close" data-popup-close aria-label="Close incident popup">✕</button>
        <div class="resqlink-popup-title">${escapeHtml(point.id || 'Incident')} (${escapeHtml(status)})</div>
        <div class="resqlink-popup-reporter">Reporter: ${escapeHtml(point.reporter || 'Unknown')}</div>
        <div class="resqlink-popup-category">Category: ${escapeHtml(point.category || 'Unspecified')}</div>
        <div class="resqlink-popup-members">Members inside: ${escapeHtml(point.membersInside ?? 0)}</div>
    </div>`;
}

function showMessage(element, text, isError) {
    if (!element) return;
    element.hidden = false;
    element.textContent = text;
    element.classList.toggle('error', isError);
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
