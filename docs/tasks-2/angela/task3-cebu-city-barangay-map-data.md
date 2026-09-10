# TASK3 — Cebu City Barangay Map Dataset

**Assigned owner:** Angela  
**Task:** Task 3 — Dispatcher Incident Coordination  
**Status:** Dataset prepared; application integration is pending

## Seeder data

The current `DatabaseSeeder` creates `admin`, `dispatcher`, and `volunteer`
roles. Development accounts are Admin `admin` / `admin@resqlink.local` /
`Admin@12345`, Dispatcher `Angela` / `a@gmail.com` / `admin123`, and Volunteer
`volunteer` / `volunteer@resqlink.local` / `Volunteer@12345`. These credentials
are for development only.

## Purpose

This document records the Cebu City barangay boundary dataset prepared for the
ResQLink local map. The dataset is intended to provide an offline administrative
overlay for displaying Cebu City barangay boundaries and supporting future local
barangay lookup or location-selection behavior.

## Created dataset

The filtered dataset is stored at:

```text
backend/public/maps/cebu-city-barangays.geojson
```

It is a GeoJSON `FeatureCollection` containing only Cebu City barangay boundary
features. The file contains the original boundary properties, including:

- `ADM4_EN`: barangay name
- `ADM4_PCODE`: NAMRIA barangay code
- `ADM3_EN`: parent city name
- `ADM3_PCODE`: parent city code
- `psgc_code`: Philippine Standard Geographic Code
- `psgc_name`: PSGC barangay name
- `psgc_status`: PSGC/NAMRIA matching status
- `match_confidence`: matching confidence value
- `validOn`: boundary validity date

The dataset contains both `Polygon` and `MultiPolygon` geometries. It is already
the simplified boundary output supplied by the source repository; no additional
application-side simplification was applied.

## Source and extraction

The source file was downloaded from the curated release of:

```text
https://github.com/bendlikeabamboo/barangay-boundaries-repository
```

Source asset:

```text
barangays.geojson
```

The source repository describes this release as a processed snapshot using PSA
PSGC data and NAMRIA administrative boundaries. The boundary data is dated
`2023-11-06`, and the PSGC snapshot is dated `2023-10-24`.

Cebu City was extracted using:

```text
ADM3_PCODE = PH0702217
```

The source properties use `PH0702217` for the Cebu City parent municipality/city
code, while the output barangay features use the corresponding Cebu City PSGC
codes beginning with `0730600001`.

## Verification

The extracted file was checked before being stored in the project:

- 80 Cebu City features were found.
- All 80 barangay names are unique.
- All features have polygon geometry.
- Geometry types are `Polygon` or `MultiPolygon`.
- All 80 features have `psgc_status` set to `fuzzy` in the source output.
- All 80 features have a source `match_confidence` value of `1.0`.

The `fuzzy` status is retained from the source dataset and should not be changed
without comparing the names and codes against an official PSA PSGC reference.

## Intended project usage

The file is a static public map asset and can later be loaded by the Leaflet map
from:

```text
/maps/cebu-city-barangays.geojson
```

It should be used as a local GeoJSON overlay for:

- Showing Cebu City barangay boundaries.
- Displaying barangay names on hover or selection.
- Supporting offline map location selection.
- Supporting future point-in-polygon barangay identification.
- Providing context for incident latitude and longitude values.

Incident markers and impact-radius circles should remain separate from this
dataset and continue to use the incident values stored in the ResQLink database.
The barangay polygons should not be treated as incident records.

No route, controller, Blade view, JavaScript, or configuration integration has
been added as part of this data-preparation change.

## Attribution and licensing

Downstream project documentation should credit:

```text
Barangay boundaries: NAMRIA, administrative-boundary shapefile version 2023-11-06.
PSGC reference data: Philippine Statistics Authority.
Dataset processing: bendlikeabamboo/barangay-boundaries-repository.
```

The processing repository publishes its code under MIT and requests attribution
to PSA and NAMRIA for the redistributed boundary data. The project should retain
this attribution when the dataset is used in the application or presentation.

## Additional local OpenStreetMap datasets

The larger BBBike extract used for local map reference data was:

```text
planet_123.7679,10.1737_124.1319,10.3967.osm.geopackage.zip
```

It covers longitude `123.7679` to `124.1319` and latitude `10.1737` to `10.3967`.
The extract was created on September 10, 2026 and contains OpenStreetMap data
through September 9, 2026. Because BBBike exports a rectangle, nearby areas
outside Cebu City are included; the prepared outputs were clipped against the
Cebu City barangay boundaries.

| File | Contents | Feature count |
| --- | --- | ---: |
| `cebu-city-osm-roads.geojson` | Roads and paths | 14,067 |
| `cebu-city-osm-landmarks.geojson` | Named OpenStreetMap points of interest | 3,516 |
| `cebu-city-osm-emergency-facilities.geojson` | Detected hospitals, clinics, fire stations, police facilities, and shelters | 48 |

The corresponding public paths are `/maps/cebu-city-osm-roads.geojson`,
`/maps/cebu-city-osm-landmarks.geojson`, and
`/maps/cebu-city-osm-emergency-facilities.geojson`. These are prepared reference
layers only and are not yet loaded by the application. Emergency-facility
coverage depends on OpenStreetMap mapping and must be reviewed before being
treated as authoritative. OpenStreetMap attribution and ODbL obligations apply.
