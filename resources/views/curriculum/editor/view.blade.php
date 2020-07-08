
@extends('layouts.left')

@section('title', "Editors")

@if (!empty($path))
    @include('component.block.path')
@endif

@section('sidebar')@endsection

@section('content')
    <h2>Editors</h2>
    <p>Life is a team sport! Authors are able to share paths with other editors to get assistance creating the best possible learning experiences.</p>
    <hr/>
    <ul>
        @foreach ($editors as $id => $info)
            <li>{{ $info['name'] }}
                @if($info['access'] == 'read') * @endif
                @if($info['access'] == 'write') ** @endif
                @if($id == $created_by_id) *** @endif
            </li>
        @endforeach
    </ul>
    <small>* reader</small>
    <small>** editor</small>
    <small>*** author</small>
@endsection
