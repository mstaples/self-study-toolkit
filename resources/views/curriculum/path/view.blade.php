
@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/questions/view/' . $pathId)) }}
@endsection

@section('sidebar')
    <section class="box">
        {{ Form::label('question_difficulty', 'question difficulty: '.ucfirst($question->question_difficulty)) }}
        <br/>
        {{ Form::submit('Continue to editors') }}
    </section>
@endsection

@section('content')
        {{ Form::label('question', 'question: '.$question->question) }}<br/>
        {{ Form::label('answer_options', 'answer options:') }}<br/>
        <ul>
            @foreach($question->answer_options as $option => $correct)
                <li>$option@if($correct) (correct)@endif</li>
            @endforeach
        </ul>
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
