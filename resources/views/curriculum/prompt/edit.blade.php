@extends('layouts.left')

@section('title', $title)

@include('component.block.path')
@include('component.block.prompt')

@section('open-main')
    <div class="form-check">
        {{ Form::open(['url' => 'curriculum/prompt/edit/'.$path->id.'/'.$prompt->id]) }}
        @endsection

        @section('sidebar')
            <section class="box">
                {{ Form::select('prompt_step', $path->getSteps(), $prompt->prompt_path_step ) }}
                <br/><br/>
                {{ Form::submit('Save and ', [ 'class' => 'btn btn-success btn-block' ]) }}
                {{ Form::select('next', $next, 'stay' ) }}
            </section>
        @endsection

        @section('content')
            <h4>{{ Form::label('prompt_title', 'Prompt title*:') }}
                <a data-toggle="collapse" href="#prompt-info" role="button" aria-expanded="false" aria-controls="path-info"><i class="fas fa-info-circle"></i></a>
            </h4>
            <div class="collapse" id="prompt-info">
                <div class="card card-body">
                    @include('component.info.prompt')
                </div>
            </div>
            <h2>{{ Form::text('prompt_title', $prompt->prompt_title) }}</h2>
            {{ Form::label('repeatable', 'Repeatable? ') }}
            {{ Form::checkbox('repeatable', 'true', $prompt->repeatable) }}<br/>
            {{ Form::label('optional', 'Optional? ') }}
            {{ Form::checkbox('optional', 'true', $prompt->optional) }}<br/>
        <hr/>
            <p>Prompts are broken up into concise segments. Define 1 to 5 segments for this prompt:</p>
            @include('curriculum.segment.all', [ 'prompt_id' => $prompt->id ])
            {{ Form::label('key', "*Required") }}<br/>
        @endsection

        @section('close-main')
            {{ Form::close() }}
    </div>
@endsection
