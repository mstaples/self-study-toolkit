@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(['url' => 'curriculum/prompt/create/'.$path->id]) }}
        @endsection

        @section('sidebar')
            <section class="box">
                <h3>{{ $path->path_title }}</h3>
                <p>{{ $path->path_thesis }}</p>
                {{ Form::label('path_difficulty', 'Path difficulty: '.$path->path_difficulty) }}
                <br/>
                {{ Form::label('path_category', 'Path category: '.$path->path_category) }}
                <br/>
                {{ Form::label('prompt_step', 'Prompt step: ') }}
                {{ Form::select('prompt_step', $path->getSteps() ) }}
                <br/>
                {{ Form::select('next', [
                        'add' => 'Add another segment',
                        'continue' => 'Continue to Sampling Questions'
                        ], 'add' ) }}
                {{ Form::submit('Save') }}
            </section>
        @endsection

        @section('content')
            <h2>Prompts</h2>
            <p>Each learning path is composed of individual prompts, with one being presented to the learner ever few days or weeks based on their preferences. One prompt may be made up of one more multiple descrete segments containing a single focus or interaction such as a link, image, thought exercise, or multiple choice question.</p>
            <p>Earlier prompts are assumed to be prerequisites of later prompts, with the exception of the last prompt which will always be presented as the last prompt regardless of what else a learner has seen on that path.</p>
            <p>Keep in mind that the number of prompts someone sees will depend on their preferences, so it's important to have repeatable prompts to facilitate learners wanting more frequent check-ins.</p>
            <hr/>
            {{ Form::label('prompt_title', 'Prompt title:') }}
            {{ Form::text('prompt_title') }}<br/>
            {{ Form::label('repeatable', 'Repeatable? ') }}
            {{ Form::checkbox('repeatable', 'true', true) }}<br/>
            <p>Prompts are broken up into concise segments. Define 1 to 5 segments for this prompt:</p>
            @include('curriculum.segment.new')
        @endsection

        @section('close-main')
            {{ Form::close() }}
    </div>
@endsection
