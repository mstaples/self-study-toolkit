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
            {{ Form::label('prompt_title', 'Prompt title:') }}
            {{ Form::text('prompt_title') }}<br/>
            {{ Form::label('repeatable', 'Repeatable? ') }}
            {{ Form::checkbox('repeatable', true, true) }}<br/>
            <p>Prompts are broken up into concise segments. Define 1 to 5 segments for this prompt:</p>
            @include('curriculum.segment.new')
        @endsection

        @section('close-main')
            {{ Form::close() }}
    </div>
@endsection
