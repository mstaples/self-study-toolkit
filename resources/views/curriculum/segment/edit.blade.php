{{ Form::hidden('segment_id'.$index, $segment->id) }}
{{ Form::label('segment_title'.$index, "Segment title* ($index): ") }}
{{ Form::text('segment_title'.$index, $segment->segment_title) }}<br/>
{{ Form::label('prompt_segment_order'.$index, 'Segment Order: '.$segment->prompt_segment_order) }}<br/>
{{ Form::label('segment_url'.$index, "Segment url ($index): ") }}
{{ Form::text('segment_url'.$index, $segment->segment_url) }}<br/>
{{ Form::label('segment_url'.$index, "Segment image url ($index): ") }}
{{ Form::text('segment_image_url'.$index, $segment->segment_imageUrl) }}<br/>
{{ Form::label('segment_accessory_type'.$index, "Segment interaction ($index): ") }}
{{ Form::select('segment_accessory_type'.$index, $path->getInteractionOptions(), $segment->segment_accessory['type'] ) }}<br/>
{{ Form::label('segment_accessory_text'.$index, "Segment interaction label ($index): ") }}
{{ Form::text('segment_accessory_text'.$index, $segment->segment_accessory['text']['text']) }}<br/>
{{ Form::label('segment_options'.$index, "All Options ($index): ") }}
{{ Form::text('segment_options'.$index, $segment->getAccessoryOptionsString()) }}<br/>
{{ Form::label('segment_answers'.$index, "Correct Options ($index): ") }}
{{ Form::text('segment_answers'.$index, $segment->getSegmentAnswersString()) }}<br/>
{{ Form::label('segment_text'.$index, "Segment text ($index):") }}<br/>
{{ Form::textarea('segment_text'.$index, $segment->segment_text) }}
