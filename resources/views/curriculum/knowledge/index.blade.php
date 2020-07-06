
@extends('layouts.empty')

@section('title', $title)

@section('content')
    <h3>Select or create a new knowledge category</h3>
    <span class="font-italic">Select a knowledge category to see and create related sampling questions, or choose to create a new knowledge category.</span>
    {{ Form::open(array('url' => 'curriculum/knowledges')) }}
        {{ Form::select('knowledge', $options) }}
    {{ Form::submit('Select') }}
    {{ Form::close() }}
@endsection
