@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form method="POST" action="{{ $action }}" style="max-width:600px;">
    @csrf @if ($method !== 'POST')
        @method($method)
    @endif
    <label>Grade level</label><select name="grade_level_id" required>
        @foreach ($gradeLevels as $level)
            <option value="{{ $level->grade_level_id }}" @selected((int) old('grade_level_id', $period?->grade_level_id ?? ($selectedGradeLevel ?? 0)) === $level->grade_level_id)>{{ $level->name }}</option>
        @endforeach
    </select>
    <label>Name</label><input name="name" value="{{ old('name', $period?->name) }}" required>
    <label>Start time</label><input type="time" name="start_time"
        value="{{ old('start_time', $period?->start_time?->format('H:i')) }}" required>
    <label>End time</label><input type="time" name="end_time"
        value="{{ old('end_time', $period?->end_time?->format('H:i')) }}" required>
    <label>Order</label><input type="number" name="order" min="1" value="{{ old('order', $period?->order) }}"
        required>
    <label><input type="checkbox" name="is_break" value="1" @checked(old('is_break', $period?->is_break))> Break period</label>
    <button type="submit" class="btn">Save Period</button>
</form>
