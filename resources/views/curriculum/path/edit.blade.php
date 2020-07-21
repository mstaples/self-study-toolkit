
@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/path/' . $path->id)) }}
@endsection

@section('sidebar')
    <section class="box">
        <small>{{ Form::label('path_level', 'Path level:') }}</small>
        {{ Form::select('path_level', $levels, $path->path_level) }}<br/>
        <small>The level of a path should helper learners find paths that make the most sense for both their existing knowledge and their available energy.</small>
        <hr/>
        <small>{{ Form::label('path_category', 'Path type:') }}</small>
        {{ Form::select('path_category', $path->getCategories(), $path->category->id) }}<br/>
        <small>Path categories help learners figure out which paths to focus on based on their current priorities and concerns.</small>
        <hr/>
        <small>{{ Form::label('existing_knowledges', 'Topics:') }}</small><br/>
        @foreach ($knowledges as $knowledge => $has)
            {{ Form::checkbox('knowledge_'.$knowledge, $knowledge, $has ? 'checked' : '' ) }}
            {{ Form::label('knowledge_'.$knowledge, $knowledge) }}<br/>
        @endforeach
        <small>{{ Form::label('new_knowledges', 'New Topics:') }}</small><br/>
        {{ Form::text('new_knowledges') }}
    </section>
@endsection

@section('content')
            <h3>Creating Paths
                <a data-toggle="collapse" href="#path-info" role="button" aria-expanded="false" aria-controls="path-info"><i class="fas fa-info"></i></a>
            </h3>
            <div class="collapse" id="path-info">
                <div class="card card-body">
                    @include('component.info.path')
                </div>
            </div>
            <p>A Path title and thesis aim to clearly convey the core knowlege or skill the path will attempt to facilitate the learner in achieving.</p>
        {{ Form::label('path_title', 'Path title:') }}
        <h2>{{ Form::text('path_title', $path->path_title) }}</h2><br/>
        {{ Form::label('path_thesis', 'Path thesis:') }}<br/>
        {{ Form::textarea('path_thesis', $path->path_thesis) }}

        {{ Form::submit('Continue to prompts', [ 'class' =>  'btn btn-success m-2' ]) }}
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
