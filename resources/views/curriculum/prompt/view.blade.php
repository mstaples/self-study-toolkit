
@extends('layouts.empty')

@section('title', $title)

@section('content')
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
