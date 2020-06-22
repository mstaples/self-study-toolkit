
@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(array('url' => 'curriculum/questions/create/'.$pathId, 'id' => 'new-question')) }}
@endsection

@section('sidebar')
    <section class="box">
        <h2>{{ Form::label('depth', 'Question depth') }}</h2>
        <p>A question's depth reflects how strong a grasp on related concepts or skills someone likely has if they know the answer.</p>
        {{ Form::select('depth', $depths) }}
        <br/>
        @foreach ($knowledges as $knowledge)
            {{ Form::checkbox('knowledge_'.$knowledge, $knowledge ) }}
            {{ Form::label('knowledge_'.$knowledge, $knowledge) }}<br/>
        @endforeach
        <button type="submit" form="new-question" value="Submit" class="btn btn-success fa-pull-right">
            Create
        </button>
    </section>
@endsection

@section('content')
        <h2>{{ Form::label('question', 'New sampling question') }}</h2>
        <p>A sampling question aims to gage how much someone might benefit
            from more familiarity with one or more topics.</p>
        {{ Form::textarea('question') }}
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
