
@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/questions/create/'.$pathId, 'id' => 'new-question')) }}
@endsection

@section('sidebar')
    <section class="box">
        <h2>{{ Form::label('question_difficulty', 'Question difficulty') }}</h2>
        <p>A question's difficulty reflects how strong a grasp on the path's thesis concepts or skills someone likely has if they know the answer.</p>
        {{ Form::select('question_difficulty', $difficulties) }}
        <br/>
        <button type="submit" form="new-question" value="Submit" class="btn btn-success fa-pull-right">
            Create
        </button>
    </section>
@endsection

@section('content')
        <h2>{{ Form::label('question', 'Sampling question') }}</h2>
        <p>A sampling question aims to gage how much someone might benefit
            from taking themselves through this path.</p>
        {{ Form::textarea('question') }}
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
