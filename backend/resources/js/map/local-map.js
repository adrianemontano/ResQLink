import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

export const CEBU_CITY_CENTER = [123.8854, 10.3157];

export const LOCAL_MAP_DATASETS = {
    barangays: '/maps/cebu-city-barangays.geojson',
    roads: '/maps/cebu-city-osm-roads.geojson',
    landmarks: '/maps/cebu-city-osm-landmarks.geojson',
    facilities: '/maps/cebu-city-osm-emergency-facilities.geojson',
};

export function createLocalMap(container, options = {}) {
    return new maplibregl.Map({
        container,
        style: window.RESQLINK_LOCAL_MAP_STYLE_URL || 'http://localhost:8080/styles/basic-preview/style.json',
        center: options.center || CEBU_CITY_CENTER,
        zoom: options.zoom || 13,
        attributionControl: false,
    });
}

export function addLocalMapControls(map, navigationPosition = 'top-right') {
    map.addControl(new maplibregl.NavigationControl(), navigationPosition);
    map.addControl(new maplibregl.AttributionControl({
        customAttribution: window.RESQLINK_LOCAL_MAP_ATTRIBUTION || '© OpenStreetMap contributors © OpenMapTiles',
    }), 'bottom-right');
}

export async function loadGeoJson(url) {
    const response = await fetch(url, { headers: { Accept: 'application/geo+json' } });
    if (!response.ok) throw new Error(`${response.status} ${response.statusText}`);
    return response.json();
}

export async function loadLocalMapDatasets(onError = () => {}) {
    const datasets = {};
    await Promise.all(Object.entries(LOCAL_MAP_DATASETS).map(async ([name, url]) => {
        try {
            datasets[name] = await loadGeoJson(url);
        } catch (error) {
            onError(name, error);
        }
    }));
    return datasets;
}

export function featureName(feature) {
    const properties = feature?.properties || {};
    return properties.ADM4_EN || properties.psgc_name || properties.name
        || properties.NAME || properties.fname || properties.amenity || '';
}

export function featureBounds(feature) {
    const coordinates = [];
    const visit = (value) => {
        if (!Array.isArray(value) || value.length === 0) return;
        if (Array.isArray(value[0])) value.forEach(visit);
        else if (value.length >= 2 && value.every(Number.isFinite)) coordinates.push(value);
    };
    visit(feature?.geometry?.coordinates || []);
    if (!coordinates.length) return null;
    return coordinates.reduce(
        (bounds, coordinate) => bounds.extend(coordinate),
        new maplibregl.LngLatBounds(coordinates[0], coordinates[0]),
    );
}

export function radiusPolygon(position, radius) {
    const points = [];
    const latitudeFactor = 111320;
    const longitudeFactor = latitudeFactor * Math.cos((position.lat * Math.PI) / 180);
    for (let index = 0; index <= 64; index += 1) {
        const angle = (index / 64) * Math.PI * 2;
        points.push([
            position.lng + (Math.cos(angle) * radius) / longitudeFactor,
            position.lat + (Math.sin(angle) * radius) / latitudeFactor,
        ]);
    }
    return { type: 'Feature', geometry: { type: 'Polygon', coordinates: [points] }, properties: {} };
}

export function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (character) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;',
    }[character]));
}

export { maplibregl };
