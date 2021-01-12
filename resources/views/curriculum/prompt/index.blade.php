
@extends('layouts.left')

@section('title', $title)

@if (!empty($path))
    @include('component.block.path')
@endif

@section('sidebar')@endsection

@section('content')
    <h2>Prompts
        <a data-toggle="collapse" href="#prompt-info" role="button" aria-expanded="false" aria-controls="path-info"><i class="fas fa-info-circle"></i></a>
    </h2>
    <div class="collapse" id="prompt-info">
        <div class="card card-body">
            @include('component.info.prompt')
        </div>
    </div>
    <p>Each learning path is composed of individual prompts, with one being presented to the learner ever few days or weeks based on their preferences. One prompt may be made up of one more multiple descrete segments containing a single focus or interaction such as a link, image, thought exercise, or multiple choice question.</p>
    {{ Form::open(array('url' => 'curriculum/prompts/'.$pathId)) }}
    {{ Form::label('prompt', 'Select or create a new prompt:') }}
    {{ Form::select('prompt', $options) }}
    {{ Form::submit('Select') }}
    {{ Form::close() }}
@endsection
