@extends('layouts.dashboard', ['title' => 'Incidents | ResQLink'])

@php
    $categoryOptions = ['' => 'All categories'] + collect($categories)
        ->mapWithKeys(fn ($category) => [$category => $category])
        ->all();
    $statusOptions = ['' => 'All statuses'] + collect($statuses)
        ->mapWithKeys(fn ($status) => [$status => $status])
        ->all();
    $barangayOptions = collect($barangays)
        ->mapWithKeys(fn ($barangay) => [$barangay => $barangay])
        ->all();
@endphp

@section('content')
    <div class="header-row">
        <div>
            <h1>Incident Management</h1>
            <p>Live queue of emergencies reported by Barangay Responders.</p>
        </div>
    </div>

    <form class="card filter-row" method="GET" action="{{ route('dispatcher.incidents.index') }}">
        <label>Category
            <x-dropdown
                name="category"
                :options="$categoryOptions"
                :value="request('category', '')"
                placeholder="All categories"
            />
        </label>
        <label>Status
            <x-dropdown
                name="status"
                :options="$statusOptions"
                :value="request('status', '')"
                placeholder="All statuses"
            />
        </label>
        <label>Barangay
            <x-dropdown
                name="barangay"
                :options="$barangayOptions"
                :value="request('barangay', '')"
                placeholder="Search or select barangay"
                :searchable="true"
            />
        </label>
        <button class="button secondary dispatcher-view-button" type="submit">Filter</button>
        <a class="button secondary dispatcher-view-button" href="{{ route('dispatcher.incidents.index') }}">Clear</a>
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
