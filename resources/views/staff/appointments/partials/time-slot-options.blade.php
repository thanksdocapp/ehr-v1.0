{{-- Fallback times when API slots unavailable: same step as duration (5–120 min, multiple of 5). Default 30. --}}
@php
    $selected = $selectedTime ?? old('appointment_time');
    if ($selected instanceof \Carbon\CarbonInterface) {
        $selected = $selected->format('H:i');
    }
    $selected = $selected ? substr((string) $selected, 0, 5) : null;

    $inc = isset($incrementMinutes) ? (int) $incrementMinutes : 30;
    $inc = max(5, min(120, $inc));
    $inc = (int) (round($inc / 5) * 5);
    $inc = max(5, $inc);

    $startMin = 8 * 60;
    $endMin = 17 * 60 + 30;
@endphp
<option value="">Select time</option>
@for($m = $startMin; $m <= $endMin; $m += $inc)
    @php
        $h = intdiv($m, 60);
        $min = $m % 60;
        $time = sprintf('%02d:%02d', $h, $min);
        $displayTime = \Carbon\Carbon::createFromFormat('H:i', $time)->format('g:i A');
    @endphp
    <option value="{{ $time }}" @selected($selected === $time)>{{ $displayTime }}</option>
@endfor
