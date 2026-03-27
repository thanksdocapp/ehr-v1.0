@extends('errors.layout-ehr')

@section('title', __('errors.web.too_many_requests_title'))

@section('content')
    @include('errors.partials.panel', [
        'icon' => 'fa-gauge-high',
        'tone' => 'warning',
        'heading' => __('errors.web.too_many_requests_title'),
        'body' => __('errors.web.too_many_requests'),
    ])
@endsection
