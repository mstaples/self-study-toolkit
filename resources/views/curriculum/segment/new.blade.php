@include('curriculum.segment.options', [
    'index' => 0,
    'prompt_id' => $prompt_id,
    'segment' => 'new',
    'last' => true
])
<div class="card alert-info m-2 collapse" id="segment0">
    <div class="card-body">
        <h2>Add a new segment</h2>
{{ Form::hidden('question_id0', 0) }}
{{ Form::label('segment_title0', "Segment title*:") }}
{{ Form::text('segment_title0') }}<br/>
{{ Form::label('accessory_type0', "Segment type*: ") }}
{{ Form::select('accessory_type0', $path->getInteractionOptions() ) }}<br/>
{{ Form::label('segment_url0', "Segment url (optional):") }}
{{ Form::text('segment_url0') }}<br/>
{{ Form::label('segment_image_url0', "Segment image url (optional):") }}
{{ Form::text('segment_image_url0') }}<br/>
{{ Form::label('image_alt_text0', "Image alt text:") }}
{{ Form::text('image_alt_text0') }}<br/>
{{ Form::label('segment_accessory_text0', "Segment interaction label (optional): ") }}
{{ Form::text('segment_accessory_text0' ) }}<br/>
{{ Form::label('segment_accessory_options0', 'All Options (optional):') }}
{{ Form::text('segment_accessory_options0') }}<br/>
{{ Form::label('segment_answers0', 'Correct Options (optional):') }}
{{ Form::text('segment_answers0') }}<br/>
{{ Form::label('segment_text0', "Segment text (optional):") }}<br/>
{{ Form::textarea('segment_text0') }}
    </div>
</div>
