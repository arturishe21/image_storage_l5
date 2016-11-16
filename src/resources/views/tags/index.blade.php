@extends('admin::layouts.default')

@section('title')
    {{__cms($title)}}
@stop

@section('main')
    @include('image-storage::tags.partials.content')
@stop


