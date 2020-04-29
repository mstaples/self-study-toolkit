
@extends('layouts.empty')

@section('title', $title)

@section('content')
    {{ Form::open(array('url' => 'curriculum/questions/' . $pathId)) }}
        {{ Form::label('question', 'Select or create a new question:') }}
        {{ Form::select('question', $options) }}
    {{ Form::submit('Select') }}
    {{ Form::close() }}
@endsection
