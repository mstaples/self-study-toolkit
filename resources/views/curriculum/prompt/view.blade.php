
@extends('layouts.left')

@section('title', $title)

@if (!empty($path))
    @include('component.block.path')
@endif

@section('sidebar')@endsection

@section('content')
    <h2>Prompts
        <a data-toggle="collapse" href="#prompt-info" role="button" aria-expanded="false" aria-controls="path-info"><i class="fas fa-info"></i></a>
    </h2>
    <div class="collapse" id="prompt-info">
        <div class="card card-body">
            @include('component.info.prompt')
        </div>
    </div>
    <p>Earlier non-optional prompts are assumed to be prerequisites of later non-optional prompts, with the exception of the last prompt which will always be presented as the last prompt regardless of what else a learner has seen on that path.</p>
    <p>Keep in mind that the number of prompts someone sees will depend on their preferences, so it's important to have repeatable prompts to facilitate learners wanting more frequent check-ins.</p>
    <hr/>
    @foreach ($prompts as $prompt)
        <h3>Prompt: {{ $prompt->prompt_title }}</h3>
        <p>Repeatable? @if ($prompt->repeatable) Yes @else No @endif</p>
        <p>Optional? @if ($prompt->optional) Yes @else No @endif</p>
        <a class="btn btn-primary" data-toggle="collapse" href="#prompt_{{ $loop->iteration }}_segments" role="button" aria-expanded="false" aria-controls="prompt_{{ $loop->iteration }}_segments">
            Segments ({{ $prompt->prompt_segments_count }})
        </a>
        <hr/>
        <div id="prompt_{{ $loop->iteration }}_segments" class="collapse card">
            @foreach($prompt->ordered_segments as $segment)
                <div class="card-body">
                    <p class="card-title">{{ $loop->iteration }}. {{ $segment->segment_title }}</p>
                    <ul>
                        @if(! @empty($segment->segment_url))
                            <li>Url: {{ $segment->segment_url }}</li>
                        @endif
                        @if(! @empty($segment->segment_image_url))
                            <li>Image url: {{ $segment->segment_image_url }}</li>
                        @endif
                        @if(! @empty($segment->segment_accessory))
                            <li>Input Type: {{ $segment->segment_accessory['type'] }}</li>
                        @endif
                        @if (! @empty($segment->segment_accessory['text']))
                            <li>Input Text: {{ $segment->segment_accessory['text']['text'] }}</li>
                        @endif
                        @if (! @empty($segment->segment_accessory_options))
                            <li>options:</li>
                            <ul>
                                @foreach($segment->segment_accessory_options as $option)
                                    <li>{{ $option->option }}
                                    @if ($option->correct)
                                        *
                                    @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </ul>
                    @if(! @empty($segment->segment_text))
                        <p>{{ $segment->segment_text }}</p>
                    @endif
                </div>
                <hr/>
            @endforeach
        </div>
    @endforeach
@endsection
