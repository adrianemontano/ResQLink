@extends('layouts.app', ['title' => '419 Session Expired | ResQLink'])

@section('body')
    <x-error-page code="419" message="Your session has expired. Please go back and try again." />
@endsection
