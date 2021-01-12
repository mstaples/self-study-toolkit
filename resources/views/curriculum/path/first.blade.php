
@extends('layouts.first')

@section('title', $title)

@section('content')
    <h2>Welcome, {{ $name }}!</h2>
    @include('component.info.path')
    <hr/>
    <p>Here's a video of what a user would experience</a> if they opted into the app right now, with a single option available to them. Following is a description of how the curriculum components you can create here, go into that user experience.</p>
    <iframe width="560" height="315" src="https://www.youtube.com/embed/PH38I8a4Ejc" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
    <hr/>
    <p>Once a user has opted into the app and plugged in their basic preferences, they first see a few Questions, which are linked to knowledge concepts and try to gage a user's current understanding in order to help surface the most useful Paths.</p>
    <p>Users get a few more of these Questions in between completing a Path and selecting their next one.</p>
    <hr/>
    @include('component.info.prompt')
    <hr/>
    <p>Before creating a new Path, you are able to take a look at the sample Path, Circle of Trust, both in the curriculum editor, and through the app. You will also be able to look at, and potentially edit, other curriculum authors' Paths if they've chosen to give you access. Later, you'll be able to invite authors to read or collaborate on Paths that you author.</p>
    <p>When you view a Path in this curriculum editor, you'll notice buttons to Demo a Path in Slack.</p>
    <p>The first time you select this option, you'll be asked to connect your user in the Slack app to your curriculum author account.</p>
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
