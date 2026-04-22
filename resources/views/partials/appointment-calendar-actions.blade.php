@if(isset($calendarLinks) && !empty($calendarLinks['google_url']) && !empty($calendarLinks['ics_url']))
<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="mb-3"><i class="fas fa-calendar-plus me-2"></i>Add to your calendar</h6>
        <div class="d-flex flex-column flex-md-row gap-2 justify-content-center">
            <a href="{{ $calendarLinks['google_url'] }}" target="_blank" rel="noopener" class="btn btn-outline-danger">
                <i class="fab fa-google me-2"></i>Google Calendar
            </a>
            <a href="{{ $calendarLinks['ics_url'] }}" class="btn btn-outline-primary">
                <i class="fas fa-download me-2"></i>Apple / Outlook (.ics)
            </a>
        </div>
    </div>
</div>
@endif
