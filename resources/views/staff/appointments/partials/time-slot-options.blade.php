{{-- 15-minute increments, 8:00–17:30 (same end window as previous 30-min grid). --}}
@php
    $selected = $selectedTime ?? old('appointment_time');
    if ($selected instanceof \Carbon\CarbonInterface) {
        $selected = $selected->format('H:i');
    }
    $selected = $selected ? substr((string) $selected, 0, 5) : null;
@endphp
<option value="">Select time</option>
@for($hour = 8; $hour <= 17; $hour++)
    @for($minute = 0; $minute < 60; $minute += 15)
        @if($hour === 17 && $minute > 30)
            @break
        @endif
        @php
            $time = sprintf('%02d:%02d', $hour, $minute);
            $displayTime = \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A');
        @endphp
        <option value="{{ $time }}" @selected($selected === $time)>{{ $displayTime }}</option>
    @endfor
@endfor
