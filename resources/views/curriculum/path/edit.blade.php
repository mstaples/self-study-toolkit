
@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/path/' . $path->id)) }}
@endsection

@section('sidebar')
    <section class="box">
        {{ Form::label('path_difficulty', 'Path difficulty:'.$path->path_difficulty) }}
        {{ Form::select('path_difficulty', $difficulties, $path->path_difficulty) }}
        <br/>
        {{ Form::label('path_category', 'Path category:') }}
        {{ Form::select('path_category', $path->getCategories(), $path->category->id) }}
        <br/>
        {{ Form::label('existing_tags', 'Existing tags:') }}<br/>
        @foreach ($path->getTags() as $tag)
            {{ Form::checkbox('tag_'.$tag, $tag, $path->hasTag($tag)) }}
            {{ Form::label('tag_'.$tag, $tag) }}<br/>
        @endforeach
        {{ Form::label('new_tags', 'New tags:') }}<br/>
        {{ Form::text('new_tags') }}<br/>
        {{ Form::submit('Continue to prompts') }}
    </section>
@endsection

@section('content')
        {{ Form::label('path_title', 'Path title:') }}
        {{ Form::text('path_title', $path->path_title) }}<br/>
        {{ Form::label('path_thesis', 'Path thesis:') }}<br/>
        {{ Form::textarea('path_thesis', $path->path_thesis) }}
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
