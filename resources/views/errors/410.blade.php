@extends('errors.layout-ehr')

@section('title', __('errors.web.gone_title'))

@section('content')
    @include('errors.partials.panel', [
        'icon' => 'fa-box-archive',
        'tone' => 'muted',
        'heading' => __('errors.web.gone_title'),
        'body' => __('errors.web.gone'),
    ])
@endsection
