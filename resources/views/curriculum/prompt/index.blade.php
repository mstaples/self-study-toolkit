
@extends('layouts.empty')

@section('title', $title)

@section('content')
    <h2>Prompts</h2>
    <p>Each learning path is composed of individual prompts, with one being presented to the learner ever few days or weeks based on their preferences. One prompt may be made up of one more multiple descrete segments containing a single focus or interaction such as a link, image, thought exercise, or multiple choice question.</p>
    {{ Form::open(array('url' => 'curriculum/prompts/'.$pathId)) }}
    {{ Form::label('prompt', 'Select or create a new prompt:') }}
    {{ Form::select('prompt', $options) }}
    {{ Form::submit('Select') }}
    {{ Form::close() }}
@endsection
