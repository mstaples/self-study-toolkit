<div id="all-segments">
    @foreach ($segments as $segment)
        <div id="segment-{{ $loop->iteration }}" class="prompt-{{ $prompt_id }}-segment">
            @include('curriculum.segment.edit', [ 'index' => $loop->iteration, 'segment' => $segment,'last' => $loop->last, 'accessory' => $segment->getAccessory() ])
        </div>
    @endforeach
    <div id="segment-0" class="prompt-{{ $prompt_id }}-segment">
        @include('curriculum.segment.new', [ 'prompt_id' => $prompt_id ])
        <hr/>
    </div>
</div>
