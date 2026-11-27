@extends('layouts.app', ['title' => '403 Forbidden | ResQLink'])

@section('body')
    <x-error-page code="403" message="You do not have permission to access this page." />
@endsection
