@extends(config('backup-browse.layout') ?? 'backup-browse::layouts.app')

@section(config('backup-browse.content_section', 'content'))
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">Edit Schedule: {{ $schedule->name }}</h2>
                <a href="{{ route('backup-browse.schedules.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to Schedules</a>
            </div>
        </div>

        <form action="{{ route('backup-browse.schedules.update', $schedule) }}" method="POST" class="px-6 py-4">
            @csrf
            @method('PUT')

            @include('backup-browse::schedules._form')

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Update Schedule
                </button>
            </div>
        </form>
    </div>
@endsection
