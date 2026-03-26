{{-- Every option is a multiple of 15 minutes (matches SlotAvailabilityService / availability API). --}}
@php
    $selected = (string) ($selectedDuration ?? '30');
@endphp
@for($minutes = 15; $minutes <= 480; $minutes += 15)
    <option value="{{ $minutes }}" @selected($selected === (string) $minutes)>
        @if($minutes < 60)
            {{ $minutes }} min
        @elseif($minutes === 60)
            1 hour
        @elseif($minutes === 90)
            1.5 hours
        @elseif($minutes === 120)
            2 hours
        @elseif($minutes % 60 === 0)
            {{ (int) ($minutes / 60) }} hours
        @else
            {{ intdiv($minutes, 60) }} hr {{ $minutes % 60 }} min
        @endif
    </option>
@endfor
