@extends('layouts.left')

@section('title', "Editors")

@include('component.block.path')

@section('content')
        {{ Form::open([ 'url' => 'curriculum/editors/'.$path->id ]) }}

        <h3>Editors</h3>
        <p>Life is a team sport! Authors are able to share paths with other editors to get assistance creating the best possible learning experiences.</p>
        <small>* author</small>
        <hr/>
        <ul>
            @foreach ($editors as $id => $info)
                <li style="list-style: none;">
                    @if($id != $created_by_id)
                        {{ Form::select('editor_'.$id, $options, $info['selected'] )}}
                    @else
                        *
                    @endif
                {{ Form::label('editor_'.$id, $info['name']) }}</li>
            @endforeach
        </ul>
        {{ Form::submit('Save', [ 'class' =>  'btn btn-success m-2' ]) }}
        {{ Form::close() }}
@endsection
