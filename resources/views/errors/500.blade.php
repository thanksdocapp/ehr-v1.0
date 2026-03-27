@extends('errors::minimal')

@section('title', __('errors.web.server_error_title'))
@section('code', '500')
@section('message', __('errors.web.server_error'))
