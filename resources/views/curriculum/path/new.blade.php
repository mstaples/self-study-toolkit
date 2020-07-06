
@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/path')) }}
@endsection

@section('sidebar')
    <section class="box">
        <small>{{ Form::label('path_level', 'Path level:') }}</small>
        {{ Form::select('path_level', $levels) }}
        <small>The level of a path should helper learners find paths that make the most sense for both their existing knowledge and their available energy.</small>
        <hr/>
        <small>{{ Form::label('path_category', 'Path type:') }}</small>
        {{ Form::select('path_category', $path->getCategories()) }}
        <small>Path categories help learners figure out which paths to focus on based on their current priorities and concerns.</small><br/>
        <hr/>
        <small>{{ Form::label('existing_knowledges', 'Topics:') }}</small><br/>
        @foreach ($knowledges as $knowledge)
            {{ Form::checkbox('knowledge_'.$knowledge, $knowledge ) }}
            {{ Form::label('knowledge_'.$knowledge, $knowledge) }}<br/>
        @endforeach
    </section>
@endsection

@section('content')
            <h4>Paths</h4>
            <p>A Path title and thesis aim to clearly convey the core knowlege or skill the path will attempt to facilitate the learner in achieving.</p>
        {{ Form::label('path_title', 'Path title:') }}
        <h3>{{ Form::text('path_title') }}</h3><br/>
        {{ Form::label('path_thesis', 'Path thesis:') }}<br/>
        {{ Form::textarea('path_thesis') }}

        {{ Form::submit('Continue to prompts', [ 'class' =>  'btn btn-success m-2' ]) }}
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
