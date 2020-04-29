<div class="btn-group" role="group" aria-label="segment options">
    @if ($index > 1)
    <button type="button" class="btn btn-info"
            onclick="updateContent(
                '{{ url('curriculum/segments/up/'.$segment->id) }}',
                'all-segments'
                )">
        <i class="fas fa-arrow-up"></i>
        Move earlier
    </button>
    @endif

    @if (!$last)
    <button type="button" class="btn btn-info"
            onclick="updateContent(
                '{{ url('curriculum/segments/down/'.$segment->id) }}',
                'all-segments'
                )">
        <i class="fas fa-arrow-down"></i>
        Move later
    </button>
    @endif

    <button type="button" class="btn btn-info"
            onclick="updateContent(
                '{{ url('curriculum/segments/edit/'.$segment->id) }}',
                'segment-{{ $segment->id }}'
                )">
        Save changes
    </button>

    <button type="button" class="btn btn-danger"
            onclick="deleteContent(
                '{{ url('curriculum/segments/delete/'.$segment->id) }}',
                'segment-{{ $segment->id }}',
                'segment'
                )">
        Delete segment
    </button>
</div>
