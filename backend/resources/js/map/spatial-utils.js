/**
 * Pure JavaScript spatial utilities for point-in-polygon queries.
 * GeoJSON coordinates are [longitude, latitude].
 */

export function pointInRing(lat, lng, ring) {
    let inside = false;
    const len = ring.length;

    for (let i = 0, j = len - 1; i < len; j = i++) {
        const xi = ring[i][0];
        const yi = ring[i][1];
        const xj = ring[j][0];
        const yj = ring[j][1];

        const intersects = ((yi > lat) !== (yj > lat))
            && (lng < ((xj - xi) * (lat - yi)) / (yj - yi) + xi);

        if (intersects) {
            inside = !inside;
        }
    }

    return inside;
}

export function isPointInPolygonGeometry(lat, lng, geometry) {
    if (!geometry || !geometry.coordinates) return false;

    if (geometry.type === 'Polygon') {
        const [outerRing, ...holes] = geometry.coordinates;
        if (!pointInRing(lat, lng, outerRing)) return false;
        for (const hole of holes) {
            if (pointInRing(lat, lng, hole)) return false;
        }
        return true;
    }

    if (geometry.type === 'MultiPolygon') {
        for (const polyCoords of geometry.coordinates) {
            const [outerRing, ...holes] = polyCoords;
            if (pointInRing(lat, lng, outerRing)) {
                let inHole = false;
                for (const hole of holes) {
                    if (pointInRing(lat, lng, hole)) {
                        inHole = true;
                        break;
                    }
                }
                if (!inHole) return true;
            }
        }
    }

    return false;
}

export function computeBoundingBox(geometry) {
    let minLng = Infinity;
    let minLat = Infinity;
    let maxLng = -Infinity;
    let maxLat = -Infinity;

    function processRing(ring) {
        for (let i = 0; i < ring.length; i++) {
            const [lng, lat] = ring[i];
            if (lng < minLng) minLng = lng;
            if (lng > maxLng) maxLng = lng;
            if (lat < minLat) minLat = lat;
            if (lat > maxLat) maxLat = lat;
        }
    }

    if (geometry.type === 'Polygon') {
        processRing(geometry.coordinates[0]);
    } else if (geometry.type === 'MultiPolygon') {
        for (const poly of geometry.coordinates) {
            processRing(poly[0]);
        }
    }

    return [minLng, minLat, maxLng, maxLat];
}

export function findEnclosingFeature(lat, lng, featuresWithBbox) {
    for (const item of featuresWithBbox) {
        const [minLng, minLat, maxLng, maxLat] = item.bbox;
        if (lng < minLng || lng > maxLng || lat < minLat || lat > maxLat) {
            continue;
        }
        if (isPointInPolygonGeometry(lat, lng, item.feature.geometry)) {
            return item;
        }
    }
    return null;
}
