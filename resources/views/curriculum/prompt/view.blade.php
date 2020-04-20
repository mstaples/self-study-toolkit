
@extends('layouts.empty')

@section('title', $title)

@section('content')
    @foreach ($prompts as $prompt)
        <h3>Prompt: {{ $prompt->prompt_title }}</h3>
        <p>Repeatable? @if ($prompt->repeatable) Yes @else No @endif</p>
        <p>Segments: {{ count($prompt->prompt_segments) }}</p>
        @foreach($prompt->prompt_segments as $segment)
            <b>{{ $loop->iteration }}. {{ $segment['title'] }}</b>
            <ul>
                @if(! @empty($segment['url']))
                    <li>Url: {{ $segment['url'] }}</li>
                @endif
                @if(! @empty($segment['imageUrl']))
                    <li>Image url: {{ $segment['imageUrl'] }}</li>
                @endif
                @if(! @empty($segment['accessory']))
                    <li>Input Type: {{ $segment['accessory']['type'] }}</li>
                @endif
                @if (! @empty($segment['accessory']['text']))
                        <li>Input Text: {{ $segment['accessory']['text']['text'] }}</li>
                @endif
                @if (! @empty($segment['accessory']['options']))
                    <li>options:</li>
                    <ul>
                        @foreach($segment['accessory']['options'] as $option)
                            <li>{{ $option['value'] }}</li>
                        @endforeach
                    </ul>
                @endif
            </ul>
            @if(! @empty($segment['text']))
                <p>{{ $segment['text'] }}</p>
            @endif
            <hr/>
        @endforeach
    @endforeach
@endsection
