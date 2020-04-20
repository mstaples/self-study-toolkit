@extends('layouts.left')

@section('title', $title)

@section('open-main')
    <div class="form-check">
        {{ Form::open(['url' => 'curriculum/prompt/edit/'.$path->id.'/'.$prompt->id]) }}
        @endsection

        @section('sidebar')
            <section class="box">
                <h3>{{ $path->path_title }}</h3>
                <p>{{ $path->path_thesis }}</p>
                {{ Form::label('path_difficulty', 'Path difficulty: '.$path->path_difficulty) }}
                <br/>
                {{ Form::label('path_category', 'Path category: '.$path->path_category) }}
                <br/>
                {{ Form::label('prompt_step', 'Prompt step: ') }}
                {{ Form::select('prompt_step', $path->getSteps(), $path->prompt_path_step ) }}
                <br/>
                {{ Form::select('next', [
                        'add' => 'Add another segment',
                        'continue' => 'Continue to Sampling Questions'
                        ], 'add' ) }}
                {{ Form::submit('Save') }}
            </section>
        @endsection

        @section('content')
            {{ Form::label('prompt_title', 'Prompt title*:') }}
            {{ Form::text('prompt_title', $prompt->prompt_title) }}<br/>
            {{ Form::label('repeatable', 'Repeatable? ') }}
            {{ Form::checkbox('repeatable', true, $prompt->repeatable) }}<br/>
            <p>Prompts are broken up into concise segments. Define 1 to 5 segments for this prompt:</p>
            @foreach ($segments as $segment)
                {{ Form::hidden('segment_id'.$loop->iteration, $segment->id) }}
                {{ Form::label('segment_title'.$loop->iteration, "Segment title* ($loop->iteration): ") }}
                {{ Form::text('segment_title'.$loop->iteration, $segment->segment_title) }}<br/>
                {{ Form::label('prompt_segment_order'.$loop->iteration, 'Segment Order: '.$segment->prompt_segment_order) }}
                {{ Form::select('prompt_segment_order'.$loop->iteration, $orderOptions, $segment->prompt_segment_order ) }}<br/>
                {{ Form::label('segment_url'.$loop->iteration, "Segment url ($loop->iteration): ") }}
                {{ Form::text('segment_url'.$loop->iteration, $segment->segment_url) }}<br/>
                {{ Form::label('segment_url'.$loop->iteration, "Segment image url ($loop->iteration): ") }}
                {{ Form::text('segment_image_url'.$loop->iteration, $segment->segment_imageUrl) }}<br/>
                {{ Form::label('segment_accessory_type'.$loop->iteration, "Segment interaction ($loop->iteration): ") }}
                {{ Form::select('segment_accessory_type'.$loop->iteration, $path->getInteractionOptions(), $segment->segment_accessory['type'] ) }}<br/>
                {{ Form::label('segment_accessory_text'.$loop->iteration, "Segment interaction label ($loop->iteration): ") }}
                {{ Form::text('segment_accessory_text'.$loop->iteration, $segment->segment_accessory['text']['text']) }}<br/>
                {{ Form::label('segment_options'.$loop->iteration, "All Options ($loop->iteration): ") }}
                {{ Form::text('segment_options'.$loop->iteration, $segment->getAccessoryOptionsString()) }}<br/>
                {{ Form::label('segment_answers'.$loop->iteration, "Correct Options ($loop->iteration): ") }}
                {{ Form::text('segment_answers'.$loop->iteration, $segment->getSegmentAnswersString()) }}<br/>
                {{ Form::label('segment_text'.$loop->iteration, "Segment text ($loop->iteration):") }}<br/>
                {{ Form::textarea('segment_text'.$loop->iteration, $segment->segment_text) }}
                <button type="button" class="btn btn-warning">Delete this segment</button>
                <hr/>
            @endforeach
            @include('curriculum.segment.new')
            {{ Form::label('key', "*Required") }}<br/>
        @endsection

        @section('close-main')
            {{ Form::close() }}
    </div>
@endsection
