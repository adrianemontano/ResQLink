@extends('layouts.dashboard', ['title' => 'Incidents | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Incident Management</h1>
            <p>Live queue of emergencies reported by volunteers.</p>
        </div>
    </div>

    <form class="card filter-row" method="GET" action="{{ route('dispatcher.incidents.index') }}">
        <label>Category
            <select name="category">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
        </label>
        <label>Status
            <select name="status">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </label>
        <label>Barangay
            <input type="search" name="barangay" value="{{ request('barangay') }}" placeholder="Search barangay">
        </label>
        <button type="submit">Filter</button>
        <a href="{{ route('dispatcher.incidents.index') }}">Clear</a>
    </form>

    <section class="card incident-results" data-incident-results aria-live="polite">
        @include('dispatcher.incidents.partials.results')
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('.filter-row');
            const results = document.querySelector('[data-incident-results]');
            if (!form || !results) return;

            const loadResults = async (url) => {
                results.setAttribute('aria-busy', 'true');
                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) throw new Error('Unable to load incidents.');
                    results.innerHTML = await response.text();
                    window.history.pushState({}, '', url);
                } catch (error) {
                    results.insertAdjacentHTML('afterbegin', '<p class="alert error">Unable to update incidents. Please try again.</p>');
                } finally {
                    results.removeAttribute('aria-busy');
                }
            };

            form.addEventListener('submit', (event) => {
                event.preventDefault();
                loadResults(new URL(form.action + '?' + new URLSearchParams(new FormData(form))).toString());
            });

            form.querySelector('a').addEventListener('click', (event) => {
                event.preventDefault();
                form.reset();
                loadResults(event.currentTarget.href);
            });

            results.addEventListener('click', (event) => {
                const link = event.target.closest('a');
                if (!link || !link.href.includes('/incidents?')) return;
                event.preventDefault();
                loadResults(link.href);
            });
        });
    </script>
@endpush
