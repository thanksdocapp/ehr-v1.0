@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'New settlement draft')
@section('page-title', 'New settlement draft')
@section('page-subtitle', 'Choose a week or month to summarize paid billings attributed to you')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.doctor-settlements.index') }}">Settlement requests</a></li>
    <li class="breadcrumb-item active">New</li>
@endsection

@section('content')
<div class="fade-in" style="max-width: 640px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h2 class="h5 mb-3">Create draft</h2>
            <p class="text-muted small">Lines are built from billings where you are the treating doctor, with a payment recorded in the selected period. You cannot create two drafts for the same period.</p>
            <form method="post" action="{{ route('staff.doctor-settlements.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Period type</label>
                    <select name="period_type" class="form-select @error('period_type') is-invalid @enderror" required>
                        <option value="weekly" {{ old('period_type') === 'weekly' ? 'selected' : '' }}>Week (calendar week containing reference date)</option>
                        <option value="monthly" {{ old('period_type', 'monthly') === 'monthly' ? 'selected' : '' }}>Month (calendar month of reference date)</option>
                    </select>
                    @error('period_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Reference date</label>
                    <input type="date" name="reference_date" class="form-control @error('reference_date') is-invalid @enderror" value="{{ old('reference_date', now()->format('Y-m-d')) }}" required>
                    @error('reference_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Context for administration">{{ old('notes') }}</textarea>
                    @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-doctor-primary">Create draft</button>
                    <a href="{{ route('staff.doctor-settlements.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
