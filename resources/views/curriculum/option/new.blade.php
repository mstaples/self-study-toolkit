<section class="box">
    <h3>New option</h3>
    {{ Form::open([ 'id' => 'option-new' ]) }}
    {{ Form::textarea('option-new-option') }}
    <br/>
    {{ Form::label('correct', 'Correct?') }}
    {{ Form::checkbox('option-new-correct', true, false) }}
    <br/>
    <button type="button" class="btn btn-success fa-pull-right"
            onclick="submitOption(
                '{{ url('curriculum/options/create/'.$questionId) }}',
                'option-new',
                'options-all'
                )">
        <i class="fas fa-check-circle"></i>
        Create
    </button>
    {{ Form::close() }}
</section>
