@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Convert Guest Patient')
@section('page-title', 'Convert Guest Patient')
@section('page-subtitle', 'Confirm name, date of birth and gender to convert this guest record')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-bold">Convert Guest Patient to Full Patient</h4>
                </div>
                
                <div class="card-body">
                    <!-- Patient Info -->
                    <div class="alert alert-info mb-4">
                        <h6 class="alert-heading"><i class="fas fa-info-circle me-2"></i>On file (reference)</h6>
                        <p class="mb-0">
                            <strong>Email:</strong> {{ $patient->email ?? '—' }}<br>
                            <strong>Phone:</strong> {{ $patient->phone }}
                        </p>
                    </div>

                    <div class="alert alert-light border mb-4" role="alert">
                        <p class="mb-0 small">
                            <strong>Faster option:</strong> from the <a href="{{ route('staff.patients.show', $patient) }}">patient record</a>, use <strong>Convert guest patient</strong> to clear guest restrictions in one step without this form.
                            For every required field in one place, use <a href="{{ route('staff.patients.edit', $patient) }}">Complete patient profile</a> instead.
                        </p>
                    </div>

                    <form action="{{ route('staff.patients.convert-guest.post', $patient) }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-semibold">First name <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name"
                                       name="first_name"
                                       value="{{ old('first_name', $patient->first_name) }}"
                                       required
                                       maxlength="255"
                                       autocomplete="given-name">
                                @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label fw-semibold">Last name <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name"
                                       name="last_name"
                                       value="{{ old('last_name', $patient->last_name) }}"
                                       required
                                       maxlength="255"
                                       autocomplete="family-name">
                                @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" 
                                   name="date_of_birth" 
                                   value="{{ old('date_of_birth', $patient->date_of_birth?->format('Y-m-d')) }}" 
                                   max="{{ date('Y-m-d', strtotime('-1 day')) }}"
                                   required>
                            @error('date_of_birth')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror" 
                                    id="gender" 
                                    name="gender" 
                                    required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Once converted, this patient will have full access to all patient portal features and you can create medical records, prescriptions, and other clinical documents for them.
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('staff.patients.show', $patient) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-check me-2"></i>Convert to Full Patient
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
