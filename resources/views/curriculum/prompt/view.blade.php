
@extends('layouts.empty')

@section('title', $title)

@section('content')
    <h2>Prompts</h2>
    <p>Each learning path is composed of individual prompts, with one being presented to the learner ever few days or weeks based on their preferences. One prompt may be made up of one more multiple descrete segments containing a single focus or interaction such as a link, image, thought exercise, or multiple choice question.</p>
    <p>Earlier prompts are assumed to be prerequisites of later prompts, with the exception of the last prompt which will always be presented as the last prompt regardless of what else a learner has seen on that path.</p>
    <p>Keep in mind that the number of prompts someone sees will depend on their preferences, so it's important to have repeatable prompts to facilitate learners wanting more frequent check-ins.</p>
    <hr/>
    @foreach ($prompts as $prompt)
        <h3>Prompt: {{ $prompt->prompt_title }}</h3>
        <p>Repeatable? @if ($prompt->repeatable) Yes @else No @endif</p>
        <p>Segments: {{ count($prompt->prompt_segments) }}</p>
        @foreach($prompt->prompt_segments as $segment)
            <b>{{ $loop->iteration }}. {{ $segment->segment_title }}</b>
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
                @if (! @empty($segment->segment_accessory['options']))
                    <li>options:</li>
                    <ul>
                        @foreach($segment->segment_accessory['options'] as $option)
                            <li>{{ $option['value'] }}</li>
                        @endforeach
                    </ul>
                @endif
            </ul>
            @if(! @empty($segment->segment_text))
                <p>{{ $segment->segment_text }}</p>
            @endif
            <hr/>
        @endforeach
    @endforeach
@endsection
