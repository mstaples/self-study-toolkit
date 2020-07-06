
@extends('layouts.left')

@section('title', $title)

@include('component.block.path')

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/path/demo/'. $path->id )) }}
@endsection

@section('sidebar')@endsection

@section('content')
            <h3>Connect your operator</h3>
            <p>To enable triggering a demo in <a href="https://slackappdemos.slack.com" target="_blank">the Slack app</a> for curriculum paths directly from the editor, you'll need to connect a slack user. Copy your slack user id here after opting into the app, and you'll get a code to connect on the app's home page.</p>
            {{ Form::label('slack_user_id', 'Slack user id:') }}
            {{ Form::text('slack_user_id') }}
            {{ Form::submit('Connect') }}
    <p>You can find your slack user id by navigating to your slack profile and then looking at the url. Your slack user id will be the string following "app/user_profile/"</p>
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
