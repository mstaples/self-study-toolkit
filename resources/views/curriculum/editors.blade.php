@extends('layouts.left')

@section('title', 'Editors')

@section('open-main')
    <div class="form-check">
        {{ Form::open(['url' => 'curriculum/editors']) }}
        @endsection

        @section('sidebar')
            <section class="box">
                {{ Form::label('existing_editors', 'Existing editors:') }}<br/>
                @foreach ($editorsOptions as $user_id => $username)
                    {{ Form::checkbox('existing_editors', $username, $user_id) }}
                    {{ Form::label('existing_editors', $username) }}<br/>
                @endforeach
                {{ Form::label('paths', 'Select paths:') }}<br/>
                @foreach ($pathOptions as $path_id => $path_title)
                    {{ Form::checkbox('paths', $path_title, $path_id) }}
                    {{ Form::label('existing_editors', $path_title) }}<br/>
                @endforeach
                {{ Form::submit('Add or Invite Editors') }}
            </section>
        @endsection

        @section('content')
            {{ Form::label('new_editors', 'Invite new editors:') }}
            {{ Form::text('new_editors') }}<br/>
            <p>You can use this field to enter a comma separated list of Twilio email addresses to
                send invitations to so they can create editor accounts and help you with the selected
                prompt paths.</p>
        @endsection

        @section('close-main')
            {{ Form::close() }}
    </div>
@endsection
