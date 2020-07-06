
@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/knowledges/create', 'id' => 'new-knowledge')) }}
@endsection

@section('sidebar')
    <section class="box">
        <h2>{{ Form::label('name', 'Name') }}</h2>
        {{ Form::text('name') }}
        <br/>
        <button type="submit" form="new-question" value="Submit" class="btn btn-success fa-pull-right">
            Create
        </button>
    </section>
            <p><small>*Some topics have prerequisites -- other topics a learner needs to have some understanding of before approaching the new topic. The more prerequisites a topic has, the more advanced the level of path it's appropriate to associate with it.</small></p>
@endsection

@section('content')
        <h2>{{ Form::label('description') }}</h2>
        {{ Form::textarea('description') }}
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
