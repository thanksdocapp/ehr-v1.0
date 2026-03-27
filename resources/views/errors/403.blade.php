@extends('errors::minimal')

@section('title', __('errors.web.forbidden_title'))
@section('code', '403')
@section('message', __('errors.web.forbidden'))
