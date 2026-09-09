@props([
    'mapId' => 'incident-map',
])

<div class="resqlink-map-wrap" data-resqlink-map>
    <div class="map-controls">
        <div class="map-search-box" aria-label="Map search">
            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6"></circle><path d="M16 16l5 5"></path></svg>
            <input type="text" placeholder="Search places, roads, or barangays" aria-label="Search map" />
        </div>

        <div class="map-status-legend" aria-label="Incident status legend">
            <button type="button" class="map-status-pill active" data-map-filter="all">
                <span class="status-dot all"></span>
                <span>All</span>
            </button>
            <button type="button" class="map-status-pill" data-map-filter="Reported">
                <span class="status-dot reported"></span>
                <span>Reported</span>
            </button>
            <button type="button" class="map-status-pill" data-map-filter="Received">
                <span class="status-dot received"></span>
                <span>Received</span>
            </button>
            <button type="button" class="map-status-pill" data-map-filter="Dispatched">
                <span class="status-dot dispatched"></span>
                <span>Dispatched</span>
            </button>
            <button type="button" class="map-status-pill" data-map-filter="Completed">
                <span class="status-dot completed"></span>
                <span>Completed</span>
            </button>
        </div>
    </div>

    <div id="{{ $mapId }}" class="resqlink-map" aria-label="Interactive Cebu City incident map"></div>
    <p class="map-message" data-map-message role="status" hidden></p>
</div>

@once
    @push('scripts')
        @vite('resources/js/dispatcher-map.js')
    @endpush
@endonce
