
@extends('layouts.empty')

@section('title', $title)

@section('content')
    {{ Form::open(array('url' => 'curriculum/paths')) }}
        {{ Form::label('path', 'Select or create a new path:') }}
        {{ Form::select('path', $options) }}
    {{ Form::submit('Select') }}
    {{ Form::close() }}
@endsection
