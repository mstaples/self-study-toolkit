
@include('curriculum.segment.options', [
    'index' => $index,
    'segment' => $segment,
    'prompt_id' => $segment->prompt->prompt_id,
    'last' => $last
    ])

<div class="card alert-info m-2 collapse" id="segment-{{ $index }}">
    <div class="card-body">
    {{ Form::hidden('question_id'.$index, $segment->id) }}
    {{ Form::label('segment_title'.$index, "Segment title*: ") }}
    {{ Form::text('segment_title'.$index, $segment->segment_title) }}<br/>
    {{ Form::label('segment_accessory_type'.$index, "Segment type*: ") }}
    {{ Form::select('segment_accessory_type'.$index, $path->getInteractionOptions(), $segment->accessory_type ) }}<br/>
    {{ Form::label('prompt_segment_order'.$index, 'Segment Order: '.$segment->prompt_segment_order) }}<br/>
    {{ Form::label('segment_url'.$index, "Segment url: ") }}
    {{ Form::text('segment_url'.$index, $segment->segment_url) }}<br/>
    {{ Form::label('segment_url'.$index, "Segment image url: ") }}
    {{ Form::text('segment_image_url'.$index, $segment->segment_imageUrl) }}<br/>
    {{ Form::label('segment_accessory_text'.$index, "Segment interaction label: ") }}
    {{ Form::text('segment_accessory_text'.$index, $accessory['text']['text']) }}<br/>
    {{ Form::label('segment_options'.$index, "All Options: ") }}
    {{ Form::text('segment_options'.$index, $segment->getAccessoryOptionsString()) }}<br/>
    {{ Form::label('segment_answers'.$index, "Correct Options: ") }}
    {{ Form::text('segment_answers'.$index, $segment->getSegmentAnswersString()) }}<br/>
    {{ Form::label('segment_text'.$index, "Segment text:") }}<br/>
    {{ Form::textarea('segment_text'.$index, $segment->segment_text) }}
    </div>
</div>
<hr/>


