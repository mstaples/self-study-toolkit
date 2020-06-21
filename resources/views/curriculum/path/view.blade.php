@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/prompts/view/' . $path->id)) }}
        @endsection

        @section('sidebar')
            <section class="box">
                {{ Form::label('path_level', 'Path level: '.ucfirst($path->path_level)) }}
                <br/>
                {{ Form::label('path_category', 'Path category: '.$path->category->name) }}
                <br/>
                {{ Form::label('existing_knowledges', 'Topics:') }}<br/>
                <ul>
                    @foreach ($knowledges as $knowledge)
                        <li>{{ $knowledge }}</li>
                    @endforeach
                </ul>
                <small>*topics with prerequisites are all student level or above</small>
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
