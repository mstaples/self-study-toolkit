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
                {{ Form::select('next', [
                        'stay' => 'Stay here',
                        'prompts' => 'select a prompt',
                        'paths' => 'select a path',
                        'questions' => 'view knowledges and questions'
                        ], 'stay' ) }}
            </section>
        @endsection

        @section('content')
            <h4>{{ Form::label('prompt_title', 'Prompt title*:') }}</h4>
            <h2>{{ Form::text('prompt_title', $prompt->prompt_title) }}</h2>
            {{ Form::label('repeatable', 'Repeatable? ') }}
            {{ Form::checkbox('repeatable', 'true', $prompt->repeatable) }}<br/>
            {{ Form::label('optional', 'Optional? ') }}
            {{ Form::checkbox('optional', 'true', $prompt->optional) }}<br/>
        <hr/>
            <p>Prompts are broken up into concise segments. Define 1 to 5 segments for this prompt:</p>
            <div id="all-segments">
                @foreach ($segments as $segment)
                    <div id="segment-{{ $loop->iteration }}" class="prompt-{{ $prompt->id }}-segment">
                        @include('curriculum.segment.edit', [ 'index' => $loop->iteration, 'segment' => $segment,'last' => $loop->last, 'accessory' => $segment->getAccessory() ])
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
