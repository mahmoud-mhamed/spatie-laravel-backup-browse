@extends(config('backup-browse.layout') ?? 'backup-browse::layouts.app')

@section(config('backup-browse.content_section', 'content'))
    <div class="bg-white shadow rounded-lg" x-data="{
        selected: [],
        allIds: @js($backups->pluck('id')->toArray()),
        get allSelected() { return this.allIds.length > 0 && this.selected.length === this.allIds.length },
        toggleAll() {
            this.selected = this.allSelected ? [] : [...this.allIds];
        }
    }">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">Backups</h2>
            <div class="flex space-x-2">
                @if(config('backup-browse.allow_full_backup'))
                    <form action="{{ route('backup-browse.run') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Backup Now
                        </button>
                    </form>
                @endif
                @if(config('backup-browse.allow_db_only_backup'))
                    <form action="{{ route('backup-browse.run') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="only_db" value="1">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            DB Only
                        </button>
                    </form>
                @endif
                @if(config('backup-browse.allow_files_only_backup'))
                    <form action="{{ route('backup-browse.run') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="only_files" value="1">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                            Files Only
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Filters --}}
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <form action="{{ route('backup-browse.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[160px]">
                    <label for="search" class="block text-xs font-medium text-gray-500 mb-1">Search</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Backup name..."
                           class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-1.5 border">
                </div>
                <div>
                    <label for="status" class="block text-xs font-medium text-gray-500 mb-1">Status</label>
                    <select name="status" id="status" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-1.5 border">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div>
                    <label for="type" class="block text-xs font-medium text-gray-500 mb-1">Type</label>
                    <select name="type" id="type" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-1.5 border">
                        <option value="">All Types</option>
                        <option value="full" {{ request('type') === 'full' ? 'selected' : '' }}>Full</option>
                        <option value="db" {{ request('type') === 'db' ? 'selected' : '' }}>DB Only</option>
                        <option value="files" {{ request('type') === 'files' ? 'selected' : '' }}>Files Only</option>
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-xs font-medium text-gray-500 mb-1">From</label>
                    <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                           class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-1.5 border">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-medium text-gray-500 mb-1">To</label>
                    <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                           class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-3 py-1.5 border">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-gray-800 text-white text-sm font-medium rounded-md hover:bg-gray-700">
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'type', 'date_from', 'date_to']))
                        <a href="{{ route('backup-browse.index') }}" class="inline-flex items-center px-3 py-1.5 bg-white text-gray-700 text-sm font-medium rounded-md border border-gray-300 hover:bg-gray-50">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Bulk Actions --}}
        <div x-show="selected.length > 0" x-cloak class="px-6 py-3 border-b border-gray-200 bg-blue-50 flex items-center justify-between">
            <span class="text-sm text-blue-800" x-text="selected.length + ' backup(s) selected'"></span>
            <form action="{{ route('backup-browse.destroy-selected') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete the selected backups?')">
                @csrf
                @method('DELETE')
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700">
                    Delete Selected
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" @click="toggleAll()" :checked="allSelected"
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($backups as $backup)
                        <tr :class="{ 'bg-blue-50/50': selected.includes({{ $backup->id }}) }">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" value="{{ $backup->id }}" x-model.number="selected"
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $backup->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div x-data="{ editing: false }" class="flex items-center">
                                    <span x-show="!editing" @dblclick="editing = true" class="cursor-pointer" title="Double-click to rename">{{ $backup->name }}</span>
                                    <form x-show="editing" @click.outside="editing = false" action="{{ route('backup-browse.rename', $backup) }}" method="POST" class="flex items-center space-x-1">
                                        @csrf
                                        @method('PUT')
                                        <input type="text" name="name" value="{{ $backup->name }}" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-2 py-1" @keydown.escape="editing = false" x-ref="input" x-init="$watch('editing', v => { if(v) $nextTick(() => $refs.input.focus()) })">
                                        <button type="submit" class="text-green-600 hover:text-green-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <button type="button" @click="editing = false" class="text-gray-400 hover:text-gray-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($backup->type)
                                    @case('db')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">DB Only</span>
                                        @break
                                    @case('files')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Files Only</span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Full</span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $backup->human_readable_size }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @switch($backup->status)
                                    @case('completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>
                                        @if(!$backup->file_exists)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 ml-1" title="File has been deleted from disk">File missing</span>
                                        @endif
                                        @break
                                    @case('in_progress')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">In Progress</span>
                                        @break
                                    @case('pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Pending</span>
                                        @break
                                    @case('failed')
                                        <div x-data="{ open: false }">
                                            <button @click="open = !open" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200 cursor-pointer">
                                                Failed
                                                <svg class="w-3 h-3 ml-1" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" x-cloak x-transition class="mt-2 p-3 bg-red-50 border border-red-200 rounded-md max-w-lg max-h-48 overflow-y-auto">
                                                <p class="text-xs font-medium text-red-800 mb-1">Error Details</p>
                                                <p class="text-xs text-red-700 whitespace-pre-wrap break-words font-mono">{{ $backup->error_message ?? 'No details available.' }}</p>
                                            </div>
                                        </div>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $backup->createdBy?->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $backup->created_at->format('M d, Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                @if($backup->status === 'completed' && $backup->path && $backup->file_exists)
                                    <a href="{{ route('backup-browse.download', $backup) }}" class="text-blue-600 hover:text-blue-900">Download</a>
                                @endif
                                <form action="{{ route('backup-browse.destroy', $backup) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this backup?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-sm text-gray-500">
                                No backups found. Click "Backup Now" to create your first backup.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-700">Per page:</span>
                <select onchange="window.location.href=this.value" class="text-sm border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 px-2 py-1 border">
                    @foreach([10, 20, 25, 50, 100] as $size)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}" {{ (int) request('per_page', 20) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                {{ $backups->links() }}
            </div>
        </div>
    </div>
@endsection
