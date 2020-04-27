@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(['url' => 'curriculum/prompt/edit/'.$path->id.'/'.$prompt->id]) }}
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
                {{ Form::select('prompt_step', $path->getSteps(), $path->prompt_path_step ) }}
                <br/>
                {{ Form::select('next', [
                        'add' => 'Add another segment',
                        'continue' => 'Continue to Sampling Questions'
                        ], 'add' ) }}
                {{ Form::submit('Save') }}
            </section>
        @endsection

        @section('content')
            {{ Form::label('prompt_title', 'Prompt title*:') }}
            {{ Form::text('prompt_title', $prompt->prompt_title) }}<br/>
            {{ Form::label('repeatable', 'Repeatable? ') }}
            {{ Form::checkbox('repeatable', 'true', $prompt->repeatable) }}<br/>
        <hr/>
            <p>Prompts are broken up into concise segments. Define 1 to 5 segments for this prompt:</p>
            <div id="all-segments">
                @foreach ($segments as $segment)
                    <div id="segment-{{ $segment->id }}">
                        @include('curriculum.segment.edit', [ 'index' => $loop->iteration, 'segment' => $segment,'last' => $loop->last ])
                        @include('curriculum.segment.options', [ 'index' => $loop->iteration, 'segment' => $segment, 'last' => $loop->last ])
                        <hr/>
                    </div>
                @endforeach
            </div>
            @include('curriculum.segment.new')
            {{ Form::label('key', "*Required") }}<br/>
        @endsection

        @section('close-main')
            {{ Form::close() }}
    </div>
@endsection
