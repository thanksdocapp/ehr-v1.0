@extends('errors.layout-ehr')

@section('title', __('errors.web.server_error_title'))

@section('content')
    @include('errors.partials.panel', [
        'icon' => 'fa-heart-pulse',
        'tone' => 'danger',
        'heading' => __('errors.web.server_error_title'),
        'body' => __('errors.web.server_error'),
        'show_sign_in' => false,
    ])
@endsection
