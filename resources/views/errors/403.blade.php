@extends('errors.layout-ehr')

@section('title', __('errors.web.forbidden_title'))

@section('content')
    @include('errors.partials.panel', [
        'icon' => 'fa-user-lock',
        'tone' => 'warning',
        'heading' => __('errors.web.forbidden_title'),
        'body' => __('errors.web.forbidden'),
    ])
@endsection
