
@extends('layouts.empty')

@section('title', $title)

@section('content')
    {{ Form::open(array('url' => 'curriculum/knowledges')) }}
        {{ Form::label('knowledge', 'Select or create a new knowledge:') }}
        {{ Form::select('knowledge', $options) }}
    {{ Form::submit('Select') }}
    {{ Form::close() }}
@endsection
