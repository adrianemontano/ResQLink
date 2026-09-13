# TASK3 — Cebu City Map Datasets

**Assigned owner:** Angela  
**Task:** Task 3 — Dispatcher Incident Coordination  
**Status:** Local datasets prepared; application integration is pending

## Development test accounts

| Role | Username | Email | Password | Web access |
| --- | --- | --- | --- | --- |
| Admin | `admin` | `admin@resqlink.local` | `Admin@12345` | Yes |
| Dispatcher | `Angela` | `a@gmail.com` | `admin123` | Yes |
| Volunteer | `volunteer` | `volunteer@resqlink.local` | `Volunteer@12345` | Yes; verified |

These are development credentials only and can be overridden with the matching `DEFAULT_*` environment variables.

## Purpose

This document records the local Cebu City map datasets prepared for the
ResQLink dispatcher map. They are intended to support an offline administrative
overlay, local roads and landmarks, emergency-facility markers, and future local
location lookup.

## Prepared files

All prepared assets are stored under:

```text
backend/public/maps/
```

| File | Contents | Verified features |
| --- | --- | ---: |
| `cebu-city-barangays.geojson` | Cebu City barangay boundaries | 80 |
| `cebu-city-osm-roads.geojson` | Roads and paths | 14,067 |
| `cebu-city-osm-landmarks.geojson` | Named OpenStreetMap points of interest | 3,516 |
| `cebu-city-osm-emergency-facilities.geojson` | Detected hospitals, clinics, fire stations, police facilities, and shelters | 48 |

All files are GeoJSON in WGS84 / EPSG:4326, which is suitable for Leaflet.

## Barangay boundaries

The barangay file was filtered from the curated `barangays.geojson` release of:

```text
https://github.com/bendlikeabamboo/barangay-boundaries-repository
```

The extraction filter was:

```text
ADM3_PCODE = PH0702217
```

Verification confirmed 80 features, 80 unique barangay names, and only Polygon
or MultiPolygon geometries. The source boundary data is dated `2023-11-06`,
with a PSGC snapshot dated `2023-10-24`. The source matching status and metadata
are retained in the output.

## OpenStreetMap extract

The roads, landmarks, and emergency-facility files were prepared from the BBBike
GeoPackage download:

```text
planet_123.7679,10.1737_124.1319,10.3967.osm.geopackage.zip
```

The source rectangle was:

```text
Longitude: 123.7679 to 124.1319
Latitude: 10.1737 to 10.3967
```

The extract was created on September 10, 2026 and contains OpenStreetMap data
through September 9, 2026. Because BBBike exports a rectangle, the source also
contains neighboring areas. The prepared outputs were clipped against the
Cebu City barangay boundary dataset to limit them to the Cebu City service area.

The original BBBike ZIP and GeoPackage are retained outside the repository as
source backups and were not modified.

## Dataset processing

The GeoPackage layers were inspected with GDAL/QGIS and processed as follows:

- `lines` was filtered to features with a non-null `highway` value and exported
  as `cebu-city-osm-roads.geojson`.
- Named `points` were exported as `cebu-city-osm-landmarks.geojson`.
- Emergency-related points were selected using OSM amenity tags and exported as
  `cebu-city-osm-emergency-facilities.geojson`.
- The outputs were spatially clipped using `cebu-city-barangays.geojson`.
- The source GeoPackage and download archive were left unchanged.

## Intended project usage

The files are static public assets and can later be requested from the browser
using these paths:

```text
/maps/cebu-city-barangays.geojson
/maps/cebu-city-osm-roads.geojson
/maps/cebu-city-osm-landmarks.geojson
/maps/cebu-city-osm-emergency-facilities.geojson
```

Suggested map responsibilities:

- Barangays: boundary overlay, names, and future point-in-polygon lookup.
- Roads: local road and path linework.
- Landmarks: named local places and points of interest.
- Emergency facilities: optional dispatch reference markers.
- Incident records: continue to come from the ResQLink database and remain
  separate from geographic reference data.

These assets are not yet integrated into the map JavaScript or Blade views.
Adding the files alone does not change the current application map.

## Limitations

The BBBike data is OpenStreetMap data, not an official Cebu City government
dataset. Emergency-facility coverage depends on what was mapped in OSM, so the
48 detected facilities should be reviewed before being treated as authoritative.
The datasets contain local geographic features but do not provide map tiles,
satellite imagery, routing, or online geocoding.

## Attribution

The project should retain the following attribution wherever these assets are
used or demonstrated:

```text
Administrative boundaries: NAMRIA, shapefile version 2023-11-06.
PSGC reference data: Philippine Statistics Authority.
Boundary processing: bendlikeabamboo/barangay-boundaries-repository.
Map features: © OpenStreetMap contributors, downloaded through BBBike.
```

OpenStreetMap-derived data is subject to the Open Database License (ODbL). The
source repository requests credit for PSA and NAMRIA boundary data.
