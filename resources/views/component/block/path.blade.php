@section('sidebar')
    <section class="box">
        <small>Path Type:</small>
        <h4>New {{ $path->category->name }}</h4>
        <span class="font-weight-bold">{{ ucfirst($path->path_level) }} Level</span>
        <hr/>
        <small>Path Topics:</small>
        <ul>
            @foreach ($path->knowledges as $knowledge)
                @if (strlen($knowledge->name) > 0)
                <li class="font-weight-bold">{{ ucfirst($knowledge->name) }}</li>
                @endif
            @endforeach
        </ul>
        <hr/>
        <h4>
            <button onclick="location.href='{{ url('/curriculum/path/demo/'.$path->id) }}'"
                 type="button" class="btn btn-info btn-update">
            Demo path in Slack
        </button></h4>
    </section>
    <hr/>
    @parent
@endsection

@section('content')
    <h1>Path: {{ $path->path_title }}
        <a data-toggle="collapse" href="#path-info" role="button" aria-expanded="false" aria-controls="path-info"><i class="fas fa-info-circle"></i></a>
    </h1>
    <div class="collapse" id="path-info">
        <div class="card card-body">
            @include('component.info.path')
        </div>
    </div>
    <p>Thesis: <span class="font-italic">{{ $path->path_thesis }}</span></p>
    <hr/>
    @parent
@endsection
