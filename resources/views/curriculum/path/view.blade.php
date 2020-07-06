@extends('layouts.left')

@section('title', $title)

@include('component.block.path')

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/prompts/view/' . $path->id)) }}
@endsection

@section('content')@endsection

@section('sidebar')
    <h3 >{{ Form::submit('Continue to prompts', [ 'class' => 'btn btn-success' ]) }}</h3 >
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
