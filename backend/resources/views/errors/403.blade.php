@extends('layouts.app', ['title' => '403 Forbidden | ResQLink'])

@section('body')
<div style="min-height: 100vh; display: flex; flex-direction: column; background: var(--surface-alt); padding: var(--space-4);">
    <div style="flex: 1; display: flex; align-items: center; justify-content: center;">
        <div class="card" style="width: 100%; max-width: 480px; text-align: center;">
            <h1 style="font-size: 48px; margin: 0 0 8px; color: var(--rosewood);">403</h1>
            <p style="color: var(--text-muted); margin: 0 0 20px;">You do not have permission to access this page.</p>
            <a class="button primary" href="{{ url('/') }}">Back to login</a>
        </div>
    </div>
    <x-footer />
</div>
@endsection
