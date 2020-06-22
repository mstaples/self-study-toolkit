
@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/path/' . $path->id)) }}
@endsection

@section('sidebar')
    <section class="box">
        <h3>Level</h3>
        <p>The level of a path should helper learners find paths that make the most sense for both their existing knowledge and their available energy.</p>
        {{ Form::label('path_level', 'Path level:'.$path->path_level) }}
        {{ Form::select('path_level', $levels, $path->path_level) }}
        <h3>Category</h3>
        <p>Path categories help learners figure out which paths to focus on based on their current priorities and concerns.</p>
        {{ Form::label('path_category', 'Path category:') }}
        {{ Form::select('path_category', $path->getCategories(), $path->category->id) }}

        {{ Form::label('existing_knowledges', 'Topics:') }}<br/>
        @foreach ($knowledges as $knowledge => $has)
            {{ Form::checkbox('knowledge_'.$knowledge, $knowledge, $has ? 'checked' : '' ) }}
            {{ Form::label('knowledge_'.$knowledge, $knowledge) }}<br/>
        @endforeach
        {{ Form::label('new_knowledges', 'New Topics:') }}<br/>
        {{ Form::text('new_knowledges') }}<br/>
        {{ Form::submit('Continue to prompts') }}
    </section>
@endsection

@section('content')
            <h3>Paths</h3>
            <p>A Path title and thesis aim to clearly convey the core knowlege or skill the path will attempt to facilitate the learner in achieving.</p>
        {{ Form::label('path_title', 'Path title:') }}
        {{ Form::text('path_title', $path->path_title) }}<br/>
        {{ Form::label('path_thesis', 'Path thesis:') }}<br/>
        {{ Form::textarea('path_thesis', $path->path_thesis) }}
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
