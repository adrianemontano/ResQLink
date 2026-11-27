@extends('layouts.dashboard', ['title' => 'Create Team | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Create Team</h1>
            <p>Group Barangay Responders into a team and assign its team lead.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.teams.index') }}">Back to Teams</a>
    </div>

    <section class="card">
        <form method="POST" action="{{ route('admin.teams.store') }}" novalidate>
            @csrf
            @include('admin.teams.partials.form', ['team' => null])

            <div class="actions">
                <button type="submit">Create Team</button>
            </div>
        </form>
    </section>
@endsection
