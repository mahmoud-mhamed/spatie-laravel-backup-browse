<div class="space-y-6">
    {{-- Name --}}
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $schedule->name ?? '') }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border px-3 py-2"
               required>
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Frequency --}}
    <div>
        <label for="frequency" class="block text-sm font-medium text-gray-700">Frequency</label>
        <select name="frequency" id="frequency"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border px-3 py-2"
                onchange="toggleFrequencyFields()">
            <option value="daily" {{ old('frequency', $schedule->frequency ?? 'daily') === 'daily' ? 'selected' : '' }}>Daily</option>
            <option value="weekly" {{ old('frequency', $schedule->frequency ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
            <option value="monthly" {{ old('frequency', $schedule->frequency ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
        </select>
        @error('frequency')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Time --}}
    <div id="time-field">
        <label for="time" class="block text-sm font-medium text-gray-700">Time</label>
        <input type="time" name="time" id="time" value="{{ old('time', $schedule->time ?? '00:00') }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border px-3 py-2">
        @error('time')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Day of Week --}}
    <div id="day-of-week-field" style="display: none;">
        <label for="day_of_week" class="block text-sm font-medium text-gray-700">Day of Week</label>
        <select name="day_of_week" id="day_of_week"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border px-3 py-2">
            <option value="0" {{ old('day_of_week', $schedule->day_of_week ?? 0) == 0 ? 'selected' : '' }}>Sunday</option>
            <option value="1" {{ old('day_of_week', $schedule->day_of_week ?? '') == 1 ? 'selected' : '' }}>Monday</option>
            <option value="2" {{ old('day_of_week', $schedule->day_of_week ?? '') == 2 ? 'selected' : '' }}>Tuesday</option>
            <option value="3" {{ old('day_of_week', $schedule->day_of_week ?? '') == 3 ? 'selected' : '' }}>Wednesday</option>
            <option value="4" {{ old('day_of_week', $schedule->day_of_week ?? '') == 4 ? 'selected' : '' }}>Thursday</option>
            <option value="5" {{ old('day_of_week', $schedule->day_of_week ?? '') == 5 ? 'selected' : '' }}>Friday</option>
            <option value="6" {{ old('day_of_week', $schedule->day_of_week ?? '') == 6 ? 'selected' : '' }}>Saturday</option>
        </select>
        @error('day_of_week')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Day of Month --}}
    <div id="day-of-month-field" style="display: none;">
        <label for="day_of_month" class="block text-sm font-medium text-gray-700">Day of Month</label>
        <input type="number" name="day_of_month" id="day_of_month" min="1" max="31"
               value="{{ old('day_of_month', $schedule->day_of_month ?? 1) }}"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm border px-3 py-2">
        @error('day_of_month')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Backup Type --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Backup Type</label>
        <div class="space-y-2">
            <label class="inline-flex items-center mr-6">
                <input type="checkbox" name="only_db" value="1"
                       {{ old('only_db', $schedule->only_db ?? false) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Database only</span>
            </label>
            <label class="inline-flex items-center">
                <input type="checkbox" name="only_files" value="1"
                       {{ old('only_files', $schedule->only_files ?? false) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <span class="ml-2 text-sm text-gray-700">Files only</span>
            </label>
        </div>
        <p class="mt-1 text-xs text-gray-500">Leave both unchecked for a full backup.</p>
    </div>

    {{-- Enabled --}}
    <div>
        <label class="inline-flex items-center">
            <input type="checkbox" name="enabled" value="1"
                   {{ old('enabled', $schedule->enabled ?? true) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            <span class="ml-2 text-sm text-gray-700">Enabled</span>
        </label>
    </div>
</div>

<script>
    function toggleFrequencyFields() {
        const frequency = document.getElementById('frequency').value;

        document.getElementById('day-of-week-field').style.display = frequency === 'weekly' ? 'block' : 'none';
        document.getElementById('day-of-month-field').style.display = frequency === 'monthly' ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', toggleFrequencyFields);
</script>
