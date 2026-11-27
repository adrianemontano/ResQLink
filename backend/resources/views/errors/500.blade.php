@extends('layouts.app', ['title' => '500 Server Error | ResQLink'])

@section('body')
    <x-error-page code="500" message="Something went wrong on our end. Please try again later." />
@endsection
