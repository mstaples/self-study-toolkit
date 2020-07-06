
@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open([
            'url' => 'curriculum/questions/edit/'.$question->id,
            'id' => 'edit-question'
        ]) }}
@endsection

@section('sidebar')
    <section class="box">
        <h2>{{ Form::label('depth', 'Question depth') }}</h2>
        <p>A question's depth reflects how strong a grasp on the path's thesis concepts or skills someone likely has if they know the answer.</p>
        {{ Form::select('depth', $question->getDepths(), $question->depth) }}
        <br/>
        <h2>Topics</h2>
        @foreach ($knowledges as $knowledge => $has)
            {{ Form::checkbox('knowledge_'.$knowledge, $knowledge, $has ? 'checked' : '' ) }}
            {{ Form::label('knowledge_'.$knowledge, $knowledge) }}<br/>
        @endforeach
        <button type="submit" form="edit-question" value="Submit" class="btn btn-success fa-pull-right">
            Save
        </button>
    </section>
@endsection

@section('content')
        <h2>{{ Form::label('question', 'Sampling question') }}</h2>
        <p>A sampling question aims to gage how much someone might benefit
            from more familiarity with the associated topics.</p>
        {{ Form::textarea('question', $question->question) }}
        <hr/>
        <h3>Options</h3>
        <p>Any number of answer options is useful. A random subset of available answer options will be selected, including at least one correct option, each time this sampling question is presented.</p>
        <div id="options-all">
            @if($question->options)
            @foreach($question->options as $option)
                <div id="option-{{ $option->id }}-div">
                    @include('curriculum.option.edit', [ 'index' => $loop->iteration, 'option' => $option,'last' => $loop->last, 'questionId' => $question->id ])
                </div>
            @endforeach
            @endif
            <div id="option-new-div">
                @include('curriculum.option.new', [ 'questionId' => $question->id ])
            </div>
        </div>
@endsection

@section('close-main')
            {{ Form::close() }}
    </div>
@endsection
