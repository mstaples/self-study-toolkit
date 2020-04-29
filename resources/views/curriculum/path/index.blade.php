
@extends('layouts.empty')

@section('title', $title)

@section('content')
    <h2>Paths</h2>
    <p>After an initial assessment, learners are offered a menu of suggested learning paths to help them train specific skills and knowledges.</p>
    {{ Form::open(array('url' => 'curriculum/paths')) }}
        {{ Form::label('path', 'Select or create a new path:') }}
        {{ Form::select('path', $options) }}
    {{ Form::submit('Select') }}
    {{ Form::close() }}
@endsection
