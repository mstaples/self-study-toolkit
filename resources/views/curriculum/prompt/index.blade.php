
@extends('layouts.empty')

@section('title', $title)

@section('content')
    {{ Form::open(array('url' => 'curriculum/prompts/'.$pathId)) }}
    {{ Form::label('prompt', 'Select or create a new prompt:') }}
    {{ Form::select('prompt', $options) }}
    {{ Form::submit('Select') }}
    {{ Form::close() }}
@endsection
