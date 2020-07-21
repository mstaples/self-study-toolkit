
@extends('layouts.empty')

@section('title', $title)

@section('content')
    <h2>Paths
        <a data-toggle="collapse" href="#path-info" role="button" aria-expanded="false" aria-controls="path-info"><i class="fas fa-info"></i></a>
    </h2>
    <div class="collapse" id="path-info">
        <div class="card card-body">
            @include('component.info.path')
        </div>
    </div>
    <p>After an initial assessment, learners are offered a menu of suggested learning paths to help them train specific skills and knowledges.</p>
    @if (session('message'))
        <div class="alert alert-info">
            {{ session('message') }}
        </div>
    @endif
    {{ Form::open(array('url' => 'curriculum/paths')) }}
        {{ Form::label('path', 'Select or create a new path:') }}
        {{ Form::select('path', $options) }}
    {{ Form::submit('Select') }}
    {{ Form::close() }}
@endsection
