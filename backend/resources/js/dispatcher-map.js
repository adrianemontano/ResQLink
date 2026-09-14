import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const PHILIPPINES = {
    center: [12.8797, 121.7740],
    bounds: L.latLngBounds([4.5, 116.0], [21.5, 127.5]),
    initialZoom: 6,
};

const markerColors = {
    incident: { Reported: '#DC2626', Received: '#D97706', Dispatched: '#16A34A', Completed: '#64748B' },
    volunteer: '#2563EB',
    dispatcher: '#7C3AED',
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

    applyBaseLayer(map);
    bindMapSearch(map, searchInput);
    bindFilterChips(map, wrapper);

    L.control.zoom({ position: 'bottomright' }).addTo(map);
    L.control.scale({ metric: true, imperial: false, position: 'bottomleft' }).addTo(map);

    const localFeatures = await addLocalFeatures(map);
    const markers = await loadMarkers(
        map,
        wrapper.dataset.markerEndpoint,
        messageElement,
        wrapper.dataset.initialMarkers,
    );
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
    const useGoogleMaps = window.USE_GOOGLE_MAPS === true;
    const apiKey = window.GOOGLE_MAPS_API_KEY || '';

    if (useGoogleMaps && apiKey) {
        const googleLayer = L.tileLayer(`https://mt0.google.com/vt/lyrs=m&x={x}&y={y}&z={z}&key=${apiKey}`, {
            maxZoom: 20,
            attribution: '&copy; Google',
        });

        googleLayer.addTo(map);
        return;
    }

    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
        subdomains: 'abcd',
        minZoom: 5,
        maxZoom: 20,
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
    }).addTo(map);
}

function bindMapSearch(map, searchInput) {
    if (!searchInput) return;

    searchInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;

        const query = searchInput.value.trim();
        if (!query) return;

        const normalized = query.toLowerCase();
        const viewbox = '116.0,4.5,127.5,21.5';
        const encodedQuery = encodeURIComponent(query);

        fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&countrycodes=ph&viewbox=${viewbox}&bounded=1&q=${encodedQuery}`, {
            headers: { Accept: 'application/json' },
        })
            .then((response) => response.ok ? response.json() : [])
            .then((results) => {
                if (!Array.isArray(results) || results.length === 0) {
                    showMessage(searchInput.closest('[data-resqlink-map]')?.querySelector('[data-map-message]'), 'No matching place was found in the Philippines.', true);
                    return;
                }

                const rankedResult = results
                    .map((result) => ({
                        ...result,
                        score: scoreSearchResult(result, normalized),
                    }))
                    .sort((first, second) => second.score - first.score)[0];

                if (!rankedResult || rankedResult.score <= 0) {
                    showMessage(searchInput.closest('[data-resqlink-map]')?.querySelector('[data-map-message]'), 'No matching place was found in the Philippines.', true);
                    return;
                }

                const latLng = L.latLng(Number(rankedResult.lat), Number(rankedResult.lon));
                map.setView(latLng, 13, { animate: true });
                L.popup()
                    .setLatLng(latLng)
                    .setContent(`<strong>${escapeHtml(rankedResult.display_name || query)}</strong>`)
                    .openOn(map);
            })
            .catch(() => {
                showMessage(searchInput.closest('[data-resqlink-map]')?.querySelector('[data-map-message]'), 'Search is temporarily unavailable.', true);
            });
    });
}

function scoreSearchResult(result, normalizedQuery) {
    const label = String(result.display_name || '').toLowerCase();
    let score = 0;

    if (label.includes(normalizedQuery)) score += 50;
    const tokens = normalizedQuery.split(/\s+/).filter(Boolean);
    tokens.forEach((token) => {
        if (label.includes(token)) score += 15;
    });

    if (result.type === 'city' || result.type === 'administrative') score += 10;
    if (result.address?.city || result.address?.town || result.address?.municipality) score += 8;
    if (result.importance) score += Number(result.importance) * 10;

    return score;
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
        const response = await fetch('/maps/resqlink-map.geojson', { headers: { Accept: 'application/geo+json' } });
        if (!response.ok) throw new Error('Local map data unavailable');

        const data = await response.json();
        const localFeatures = [];

        const roads = L.geoJSON(data, {
            filter: (feature) => feature.properties?.kind === 'road',
            style: { color: '#E6EEF5', opacity: 0.95, weight: 7 },
            onEachFeature: (feature, layer) => bindFeaturePopup(layer, feature, 'Road'),
        }).addTo(map);
        localFeatures.push(...collectLocalFeatures(roads, 'road'));

        const landmarks = L.geoJSON(data, {
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

function collectLocalFeatures(layerGroup, kind) {
    return layerGroup.getLayers()
        .filter((layer) => layer.feature?.properties?.name)
        .map((layer) => ({
            name: layer.feature.properties.name,
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
    layer.bindPopup(`<strong>${escapeHtml(feature.properties?.name || type)}</strong><br>${type} in the Cebu City local dataset.`);
}

async function loadMarkers(map, endpoint, messageElement, initialMarkers = '[]') {
    const fallbackPoints = JSON.parse(initialMarkers || '[]');

    try {
        const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Marker endpoint failed');

        const points = (await response.json()).data || [];
        if (points.length === 0 && Array.isArray(fallbackPoints) && fallbackPoints.length > 0) {
            return fallbackPoints.flatMap((point) => renderMarker(map, point));
        }
        if (points.length === 0) showMessage(messageElement, 'No incident or dispatch points are available yet.', false);

        return points.flatMap((point) => renderMarker(map, point));
    } catch {
        if (Array.isArray(fallbackPoints) && fallbackPoints.length > 0) {
            return fallbackPoints.flatMap((point) => renderMarker(map, point));
        }

        showMessage(messageElement, 'Map loaded, but incident data is temporarily unavailable.', true);
        return [];
    }
}

function renderMarker(map, point) {
    if (!Number.isFinite(Number(point.latitude)) || !Number.isFinite(Number(point.longitude))) return [];

    const type = point.type || 'incident';
    const status = normalizeStatus(point.status);
    const color = type === 'incident' ? markerColors.incident[status] || markerColors.incident.Reported : markerColors[type] || '#2563EB';
    const coordinates = [Number(point.latitude), Number(point.longitude)];
    const pinIcon = buildMapPin(color);
    const marker = L.marker(coordinates, {
        icon: pinIcon,
        keyboard: false,
    }).addTo(map);
    marker.__resqlinkStatus = status;

    const headline = point.category || point.type || 'Incident';
    const detailLines = [
        `Category: ${escapeHtml(headline)}`,
        point.reporter ? `Reporter: ${escapeHtml(point.reporter)}` : null,
        point.status ? `Status: ${escapeHtml(status)}` : null,
        point.reported_at ? `Reported: ${escapeHtml(new Date(point.reported_at).toLocaleString())}` : null,
    ].filter(Boolean);

    marker.bindPopup(`${detailLines.map((line) => `<div>${line}</div>`).join('')}${point.detail_url ? `<div><a href="${escapeHtml(point.detail_url)}">Open details</a></div>` : ''}`);

    if (Number(point.impact_radius) > 0) {
        marker.__resqlinkRadius = L.circle(coordinates, {
            color,
            fillColor: color,
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

function buildMapPin(color) {
    return L.divIcon({
        className: 'resqlink-map-pin-wrapper',
        html: `<div class="resqlink-map-pin" style="--pin-color:${color};"><span class="resqlink-map-pin-core"></span></div>`,
        iconSize: [20, 26],
        iconAnchor: [10, 26],
        popupAnchor: [0, -22],
    });
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
