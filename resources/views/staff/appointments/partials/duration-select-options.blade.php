{{-- 5-minute increments, 5–120 min (2 hours max). --}}
@php
    $selected = (string) ($selectedDuration ?? '30');
@endphp
@for($minutes = 5; $minutes <= 120; $minutes += 5)
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
