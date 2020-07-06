@extends('layouts.empty')

@section('title', $title)


@section('open-main')
        @endsection

        @section('sidebar')@endsection

        @section('content')
            <h2>Prompts</h2>
            <p>Each learning path is composed of individual prompts, with one being presented to the learner ever few days or weeks based on their preferences. One prompt may be made up of one more multiple descrete segments containing a single focus or interaction such as a link, image, thought exercise, or multiple choice question.</p>
            <p><strong>Non-optional</strong> earlier prompts are assumed to be prerequisites of <strong>non-optional</strong> later prompts, with the exception of the last prompt which will always be presented as the last prompt regardless of what else a learner has seen on that path.</p>
            <p>Keep in mind that the number of prompts someone sees will depend on their preferences, so it's important to have repeatable prompts to facilitate learners wanting more frequent check-ins.</p>
            <hr/>
            <div class="form-check">
            {{ Form::open([ 'url' => 'curriculum/prompt/create/'.$path->id] ) }}
            {{ Form::label('prompt_title', 'Prompt title:') }}
            {{ Form::text('prompt_title') }}<br/>
            {{ Form::label('repeatable', 'Repeatable? ') }}
            {{ Form::checkbox('repeatable', 'true', true) }}<br/>
            {{ Form::label('optional', 'Optional? ') }}
            {{ Form::checkbox('optional', 'true', true) }}<br/>
            {{ Form::submit('Save and add content', [ 'class' => 'btn btn-success m-2' ]) }}
        @endsection

        @section('close-main')
            {{ Form::close() }}
    </div>
@endsection
