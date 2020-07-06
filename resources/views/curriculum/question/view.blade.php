
@extends('layouts.left')

@section('title', $title)

        @section('sidebar')
            <section class="box">
                <h2>Depth: {{ $question->depth }}</h2>
                <p>A question's depth reflects how strong a grasp on the path's thesis concepts or skills someone likely has if they know the answer.</p>
                <br/>
                <h2>Topics</h2>
                @foreach ($knowledges as $knowledge => $has)
                    <li>{{ $knowledge }}</li>
                @endforeach
            </section>
        @endsection

        @section('content')
            <h2>Question: {{ $question->question }}</h2>
            <p>A sampling question aims to gage how much someone might benefit
                from more familiarity with the associated topics.</p>
            <hr/>
            <h3>Options</h3>
            <p>Any number of answer options is useful. A random subset of available answer options will be selected, including at least one correct option, each time this sampling question is presented.</p>
            <div id="options-all">
                @if($question->options)
                    @foreach($question->options as $option)
                        <li>{{ $option->option }} @if ($option->correct)(correct)@endif</li>
                    @endforeach
                @endif
            </div>
        @endsection
