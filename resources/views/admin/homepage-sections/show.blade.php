@extends('admin.layouts.app')

@section('title', 'View Homepage Section')

@section('content')


    <div class="container-fluid px-4">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h1 class="h3 mb-0 text-gray-800">View Homepage Section</h1>

            <div>

                <a href="{{ contextRoute('homepage-sections.edit', $homepageSection) }}" class="btn btn-primary me-2">

                    <i class="fas fa-edit me-1"></i>Edit

                </a>

                <a href="{{ contextRoute('homepage-sections.index') }}" class="btn btn-secondary">

                    <i class="fas fa-arrow-left me-1"></i>Back to Sections

                </a>

            </div>

        </div>



        <div class="card shadow">

            <div class="card-body">

                <div class="row mb-3">

                    <div class="col-md-12">

                        <div class="table-responsive">

                            <table class="table table-bordered">

                                <tbody>

                                    <tr>

                                        <th scope="row">Section Key</th>

                                        <td>{{ $homepageSection->section_key }}</td>

                                    </tr>

                                    <tr>

                                        <th scope="row">Badge Text</th>

                                        <td>{{ $homepageSection->badge_text ?? 'N/A' }}</td>

                                    </tr>

                                    <tr>

                                        <th scope="row">Title</th>

                                        <td>{{ $homepageSection->title }}</td>

                                    </tr>

                                    <tr>

                                        <th scope="row">Subtitle</th>

                                        <td>{{ $homepageSection->subtitle ?? 'N/A' }}</td>

                                    </tr>

                                    <tr>

                                        <th scope="row">Active Status</th>

                                        <td>{!! $homepageSection->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' !!}</td>

                                    </tr>

                                    <tr>

                                        <th scope="row">Sort Order</th>

                                        <td>{{ $homepageSection->sort_order }}</td>

                                    </tr>

                                    <tr>

                                        <th scope="row">Created At</th>

                                        <td>{{ $homepageSection->created_at->format('d-m-Y H:i:s') }}</td>

                                    </tr>

                                    <tr>

                                        <th scope="row">Updated At</th>

                                        <td>{{ $homepageSection->updated_at->format('d-m-Y H:i:s') }}</td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


@endsection

