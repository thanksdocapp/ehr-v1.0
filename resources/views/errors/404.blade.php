@extends('errors.layout-ehr')

@section('title', __('errors.web.not_found_title'))

@section('content')
    @include('errors.partials.panel', [
        'icon' => 'fa-compass',
        'tone' => 'info',
        'heading' => __('errors.web.not_found_title'),
        'body' => __('errors.web.not_found'),
    ])
@endsection
