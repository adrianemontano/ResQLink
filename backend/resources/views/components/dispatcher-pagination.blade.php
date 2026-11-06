@props(['paginator', 'perPage' => 10])

<div class="dispatcher-pagination">
    @if ($paginator->hasPages())
        {{ $paginator->onEachSide(1)->links('vendor.pagination.resqlink') }}
    @endif

    <form class="dispatcher-pagination-size" method="GET" action="{{ url()->current() }}">
        @foreach (request()->except(['page', 'per_page']) as $key => $value)
            @if (is_scalar($value))
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach

        <x-dropdown
            id="dispatcher-pagination-per-page"
            name="per_page"
            :options="['10' => '10 / page', '25' => '25 / page', '50' => '50 / page']"
            :value="(string) $perPage"
            placeholder="Items per page"
        />
    </form>
</div>

@once
    <script>
        document.addEventListener('change', (event) => {
            if (!event.target.matches('#dispatcher-pagination-per-page [data-dropdown-value]')) return;
            event.target.form?.requestSubmit();
        });
    </script>
@endonce
