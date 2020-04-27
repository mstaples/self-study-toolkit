@foreach ($segments as $segment)
    <div id="segment-{{ $segment->id }}">
        @include('curriculum.segment.edit', [ 'index' => $loop->iteration, 'segment' => $segment, 'last' => $loop->last ])
        @include('curriculum.segment.options', [ 'index' => $loop->iteration, 'segment' => $segment, 'last' => $loop->last ])
        <hr/>
    </div>
@endforeach
