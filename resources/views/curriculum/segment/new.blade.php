{{ Form::label('segment_title', "Segment title:") }}
{{ Form::text('segment_title') }}<br/>
{{ Form::label('segment_url', "Segment url (optional):") }}
{{ Form::text('segment_url') }}<br/>
{{ Form::label('segment_url', "Segment image url (optional):") }}
{{ Form::text('segment_imageUrl') }}<br/>
{{ Form::label('segment_accessory_type', "Segment interaction (optional): ") }}
{{ Form::select('segment_accessory_type', $path->getInteractionOptions() ) }}<br/>
{{ Form::label('segment_accessory_text', "Segment interaction label (optional): ") }}
{{ Form::text('segment_accessory_text' ) }}<br/>
{{ Form::label('segment_accessory_options', 'All Options (optional):') }}
{{ Form::text('segment_accessory_options') }}<br/>
{{ Form::label('segment_answers', 'Correct Options (optional):') }}
{{ Form::text('segment_answers') }}<br/>
{{ Form::label('segment_text', "Segment text (optional):") }}<br/>
{{ Form::textarea('segment_text') }}
