<section class="box">
    @include('component.message', [ 'message' => $message, 'message_role' => $message_role ])
    {{ Form::open([ 'id' => 'option-'.$option->id ]) }}
    {{ Form::textarea("option-$option->id-option", $option->option) }}
    <br/>
    {{ Form::label('correct', 'Correct?') }}
    {{ Form::checkbox("option-$option->id-correct", true, $option->correct) }}
    <br/>
    <button type="button" class="btn btn-warning"
            onclick="deleteContent(
                '{{ url('curriculum/options/delete/'.$questionId.'/'.$option->id) }}',
                'option-{{ $option->id }}-div',
                'option'
                )">
        <i class="fas fa-exclamation-triangle"></i>
        Delete
    </button>
    <button type="button" class="btn btn-success"
            onclick="submitOption(
                '{{ url('curriculum/options/edit/'.$questionId.'/'.$option->id) }}',
                'option-{{ $option->id }}',
                'option-{{ $option->id }}-div'
                )">
        <i class="fas fa-check-circle"></i>
        Save
    </button>
    {{ Form::close() }}
</section>
