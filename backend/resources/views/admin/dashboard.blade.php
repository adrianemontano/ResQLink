@extends('layouts.dashboard', ['title' => 'Admin Dashboard | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Admin Dashboard</h1>
            <p>Initial system overview for account administration and operations.</p>
        </div>
        <a class="button" href="{{ route('admin.users.create') }}">Create User</a>
    </div>

    <section class="grid">
        <article class="card stat-blue">
            <h2>Total Volunteers</h2>
            <div class="metric">{{ $totalVolunteers }}</div>
        </article>
        <article class="card stat-green">
            <h2>Total Dispatchers</h2>
            <div class="metric">{{ $totalDispatchers }}</div>
        </article>
        <article class="card stat-red">
            <h2>Total Incidents</h2>
            <div class="metric">{{ $totalIncidents }}</div>
        </article>
    </section>
@endsection
