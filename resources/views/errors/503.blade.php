@extends('errors::minimal')

@section('title', __('errors.web.service_unavailable_title'))
@section('code', '503')
@section('message', __('errors.web.service_unavailable'))
