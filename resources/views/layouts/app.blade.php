<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Browse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Navigation Tabs --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Backup Browse</h1>
            <nav class="flex space-x-4">
                <a href="{{ route('backup-browse.index') }}"
                   class="px-4 py-2 rounded-md text-sm font-medium {{ request()->routeIs('backup-browse.index') || request()->routeIs('backup-browse.run') ? 'bg-white text-blue-600 shadow' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60' }}">
                    Backups
                </a>
                <a href="{{ route('backup-browse.schedules.index') }}"
                   class="px-4 py-2 rounded-md text-sm font-medium {{ request()->routeIs('backup-browse.schedules.*') ? 'bg-white text-blue-600 shadow' : 'text-gray-600 hover:text-gray-900 hover:bg-white/60' }}">
                    Schedules
                </a>
            </nav>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Content --}}
        @yield('content')
    </div>
</body>
</html>
