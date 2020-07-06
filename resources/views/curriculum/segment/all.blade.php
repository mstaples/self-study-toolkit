@foreach ($segments as $segment)
    <div id="segment-{{ $loop->iteration }}" class="prompt-{{ $segment->prompt->prompt_id }}-segment">
        @include('curriculum.segment.options', [
            'index' => $loop->iteration,
            'segment' => $segment,
            'prompt_id' => $segment->prompt->prompt_id,
            'last' => $loop->last
            ])
        <hr/>
    </div>
@endforeach
