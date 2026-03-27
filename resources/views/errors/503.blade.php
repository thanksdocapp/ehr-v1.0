@extends('errors.layout-ehr')

@section('title', __('errors.web.service_unavailable_title'))

@section('content')
    @include('errors.partials.panel', [
        'icon' => 'fa-screwdriver-wrench',
        'tone' => 'info',
        'heading' => __('errors.web.service_unavailable_title'),
        'body' => __('errors.web.service_unavailable'),
    ])
@endsection
