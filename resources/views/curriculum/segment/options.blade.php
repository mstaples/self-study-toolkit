<div class="btn-toolbar m-2" role="toolbar" aria-label="Toolbar for this segment">
    <div class="btn-group col-6" role="group" aria-label="Open segment edit form button">
        <button
            type="button"
            class="btn btn-primary btn-secondary btn-block"
            data-toggle="collapse"
            data-target="#segment{{ $index }}"
            aria-expanded="false"
            aria-controls="segment{{ $index }}"
        >
            <i class="far fa-edit"></i>
            {{ $segment == "new" ? "New Segment" : $segment->segment_title }}</button>
    </div>

    <div class="btn-group col-6" role="group" aria-label="segment option buttons">
        @if ($index > 0)
            @if ($index > 1)
                <button type="button" class="btn btn-primary btn-secondary"
                        onclick="updateContent(
                            '{{ url('curriculum/segments/up/' . ($segment == "new" ? "" : $segment->id)) }}',
                            'all-segments'
                            )">
                    <i class="fas fa-arrow-up"></i>
                    Earlier
                </button>
            @endif
            @if (!$last)
                <button type="button" class="btn btn-primary btn-secondary"
                        onclick="updateContent(
                            '{{ url('curriculum/segments/down/' . ($segment == "new" ? "" : $segment->id)) }}',
                            'all-segments'
                            )">
                    <i class="fas fa-arrow-down"></i>
                    Later
                </button>
            @endif

            <button type="button" class="btn btn-primary btn-secondary btn-block"
                    onclick="submitSegmentForm(
                        '{{ url('curriculum/segments/edit/' . ($segment->id.'/'.$index)) }}',
                        '{{ $prompt->id }}',
                        '{{ $index }}')">
                <i class="far fa-save"></i>
                Save
            </button>

            <button type="button" class="btn btn-danger btn-secondary"
                    onclick="deleteContent(
                        '{{ url('curriculum/segments/delete/'.$segment == "new" ? "" : $segment->id) }}',
                        'segment-{{ $segment == "new" ? "" : $segment->id }}',
                        'segment-all'
                        )">
                <i class="fas fa-ban"></i>
                Delete
            </button>
        @else
            <button type="button" class="btn btn-primary btn-secondary btn-block"
                    onclick="submitSegmentForm('{{ url('curriculum/segments/new') }}', '{{ $prompt_id }}','0')">
                <i class="far fa-save"></i>
                Save
            </button>
        @endif
    </div>

</div>
