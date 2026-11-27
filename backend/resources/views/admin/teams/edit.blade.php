@extends('layouts.dashboard', ['title' => 'Edit Team | ResQLink'])

@section('content')
    <div class="header-row">
        <div>
            <h1>Edit Team</h1>
            <p>Update {{ $team->name }}'s members and team lead.</p>
        </div>
        <a class="button secondary" href="{{ route('admin.teams.index') }}">Back to Teams</a>
    </div>

    <section class="card">
        <form method="POST" action="{{ route('admin.teams.update', $team) }}" novalidate>
            @csrf
            @method('PUT')
            @include('admin.teams.partials.form', ['team' => $team])

            <div class="actions">
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </section>
@endsection
