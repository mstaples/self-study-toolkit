@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/prompts/view/' . $path->id)) }}
        @endsection

        @section('sidebar')
            <section class="box">
                {{ Form::label('path_difficulty', 'Path difficulty: '.ucfirst($path->path_difficulty)) }}
                <br/>
                {{ Form::label('path_category', 'Path category: '.$path->category->name) }}
                <br/>
                {{ Form::label('existing_tags', 'Tags:') }}<br/>
                @foreach ($path->getTags() as $tag)
                    {{ Form::label('tag_'.$tag, $tag) }}<br/>
                @endforeach
                <br/>
                {{ Form::submit('Continue to prompts') }}
            </section>
        @endsection

        @section('content')
            {{ Form::label('path_title', 'Path title: '.$path->path_title) }}<br/>
            {{ Form::label('path_thesis', 'Path thesis:') }}<br/>
            <p>{{ $path->path_thesis }}</p>
        @endsection

        @section('close-main')
            {{ Form::close() }}
    </div>
@endsection
