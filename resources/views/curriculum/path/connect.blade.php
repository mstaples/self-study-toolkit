
@extends('layouts.left')

@section('title', $title)

@include('component.block.path')

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/path/connect/' . $path->id )) }}
@endsection

@section('sidebar')@endsection

@section('content')
            <h3>Connect your operator</h3>
            <p>To enable triggering a demo in <a href="https://slackappdemos.slack.com" target="_blank">the Slack app</a> for curriculum paths directly from the editor, you'll need to connect a slack user. Select the connect code displayed here from the list of options displayed in the app home of your Slack workspace.</p>
            <div class="alert-info">
                <h2 class="alert-info">{{ $code }}</h2>
            </div>
        {{ Form::close() }}
        <hr/>
        <p>Not seeing the options in the app home of your Slack workspace? Resend it by resubmitting your slack user id:</p>
            {{ Form::open(array('url' => 'curriculum/path/demo/'. $path->id )) }}
            {{ Form::label('slack_user_id', 'Slack user id:') }}
            {{ Form::text('slack_user_id', $slack_user_id) }}
            {{ Form::submit('Resend') }}
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
