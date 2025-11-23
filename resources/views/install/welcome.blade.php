@extends('install.layout')

@section('title', 'Welcome - ThanksDoc EHR Installation')

@section('content')
<div class="text-center">
    <div class="mb-4">
        <i class="fas fa-hospital fa-4x text-primary mb-3"></i>
        <h2 class="step-title">Welcome to ThanksDoc EHR</h2>
        <p class="lead text-muted">
            Transform your healthcare facility with our comprehensive medical management solution
        </p>
    </div>
    
    <div class="row g-4 my-5">
        <div class="col-md-4">
            <div class="feature-card text-center p-4 h-100 border rounded-3">
                <i class="fas fa-shield-alt fa-3x text-success mb-3"></i>
                <h5>GDPR Compliant</h5>
                <p class="text-muted mb-0">Healthcare-grade security with full medical regulatory compliance</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card text-center p-4 h-100 border rounded-3">
                <i class="fas fa-cogs fa-3x text-warning mb-3"></i>
                <h5>Automated Workflows</h5>
                <p class="text-muted mb-0">Streamlined medical operations with intelligent healthcare automation</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="feature-card text-center p-4 h-100 border rounded-3">
                <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                <h5>Medical Analytics</h5>
                <p class="text-muted mb-0">Real-time healthcare insights and comprehensive medical reporting</p>
            </div>
        </div>
    </div>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Installation Process:</strong> This wizard will guide you through setting up your ThanksDoc EHR.
        The process typically takes 5-10 minutes and includes system checks, database setup, and admin account creation.
    </div>
    
    <div class="mb-4">
        <h5 class="mb-3">What's Included</h5>
        <div class="row text-start">
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Patient Management</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Doctor & Staff Management</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Appointment Scheduling</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Medical Records (EMR)</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Laboratory Management</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Pharmacy Management</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Billing & Insurance</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Inventory Management</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Admin Dashboard</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Medical Reports</li>
                </ul>
            </div>
        </div>
    </div>
    
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Important:</strong> Please ensure you have proper server permissions and database access before proceeding. 
        Have your database credentials ready for the setup process.
    </div>
</div>
@endsection

@section('footer')
<div class="text-muted">
    <small>
        <i class="fas fa-info-circle me-1"></i>
        {{ $productInfo['name'] }} v{{ $productInfo['version'] }} | Premium CodeCanyon Script
    </small>
</div>
<div>
    <a href="{{ route('install.step', 'requirements') }}" class="btn btn-primary btn-lg">
        <i class="fas fa-rocket me-2"></i>
        Start Installation
    </a>
</div>
@endsection

@push('styles')
<style>
    .feature-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush