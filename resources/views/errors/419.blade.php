@extends('errors.layout-ehr')

@section('title', __('errors.web.page_expired_title'))

@section('content')
    @include('errors.partials.panel', [
        'icon' => 'fa-arrows-rotate',
        'tone' => 'warning',
        'heading' => __('errors.web.page_expired_title'),
        'body' => __('errors.web.page_expired'),
        'show_sign_in' => true,
    ])
@endsection
