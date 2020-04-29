 @if($options)
    @foreach($options as $option)
        <div id="option-{{ $option->id }}-div">
            @include('curriculum.option.edit', [ 'index' => $loop->iteration, 'option' => $option,'last' => $loop->last, 'questionId' => $questionId ])
        </div>
    @endforeach
@endif
<div id="option-new-div">
    @include('curriculum.option.new', [ 'questionId' => $questionId ])
</div>
