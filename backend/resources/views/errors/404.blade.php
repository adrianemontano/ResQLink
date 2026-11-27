@extends('layouts.app', ['title' => '404 Not Found | ResQLink'])

@section('body')
    <x-error-page code="404" message="The page you are looking for could not be found." />
@endsection
