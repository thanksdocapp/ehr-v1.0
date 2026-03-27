@extends('errors::minimal')

@section('title', __('errors.web.too_many_requests_title'))
@section('code', '429')
@section('message', __('errors.web.too_many_requests'))
