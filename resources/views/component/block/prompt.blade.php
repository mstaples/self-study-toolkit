@section('sidebar')
    <section class="box">
        <h4><button
                url="{{ url('curriculum/prompt/demo/'.$prompt->id) }}"
                type="button"
                class="btn btn-info btn-update"
            >
                Demo prompt in Slack
            </button></h4>
        <p><small>*Demoing a path in Slack creates that full journey in the record of your operator so that you are able to experiment with the entire experience.<br/><br/>Demoing a <span class="font-weight-bold">prompt</span> in Slack creates a faux journey to allow you to step through that prompt, but then deletes the record once you complete the prompt.</small></p>
    </section>
    <hr/>
    @parent
@endsection
