@php
    $timetableDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    $timetableSlots = $slots instanceof \Illuminate\Support\Collection ? $slots : collect($slots);
    $timetableMap = $timetableSlots->keyBy(fn($slot) => $slot->day_of_week . '-' . $slot->period_id);
@endphp
<div style="overflow-x:auto;">
    <table>
        <thead>
            <tr>
                <th>Day</th>
                @foreach ($periods as $period)
                    <th>{{ $period->name }}<br><small>{{ $period->start_time?->format('H:i') }}-{{ $period->end_time?->format('H:i') }}</small>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($timetableDays as $day)
                <tr>
                    <th>{{ $day }}</th>
                    @foreach ($periods as $period)
                        @php($slot = $timetableMap->get($day . '-' . $period->id))
                        <td style="min-width:150px; vertical-align:top;">
                            @if ($period->is_break)
                                <strong>Break</strong>
                            @elseif ($slot)
                                <strong>{{ $slot->subject?->subject_name ?? 'Unassigned' }}</strong><br>
                                <small>{{ $slot->teacher?->full_name ?? 'Teacher unassigned' }}</small>
                            @else
                                <span style="color:#64748b;">Free</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
