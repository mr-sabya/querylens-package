<!DOCTYPE html>
<html>

<head>
    <title>QueryLens</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add this in your <head> -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-sql.min.js"></script>
</head>

<body class="bg-gray-950 text-gray-100 p-10 font-sans">
    <div class="max-w-6xl mx-auto">

        <!-- Header with Buttons -->
        <div class="flex justify-between items-end mb-8">
            <div>
                <h1 class="text-3xl font-bold text-blue-400 mb-2">QueryLens</h1>
                <p class="text-gray-400">Live Request & Query Monitor</p>
            </div>

            <div class="flex gap-3">
                <!-- Reload Button -->
                <button onclick="window.location.reload()"
                    class="flex items-center gap-2 bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg border border-gray-700 transition-all active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                        <path d="M21 3v5h-5"></path>
                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                        <path d="M8 16H3v5"></path>
                    </svg>
                    Reload
                </button>

                <!-- Clear Button -->
                <form action="/querylens/clear" method="POST">
                    @csrf
                    <button type="submit"
                        class="flex items-center gap-2 bg-red-900/20 hover:bg-red-900/40 text-red-400 px-4 py-2 rounded-lg border border-red-900/50 transition-all active:scale-95">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                        </svg>
                        Clear Logs
                    </button>
                </form>
            </div>
        </div>

        <div class="space-y-6">
            @forelse($requests as $request)
            <!-- ... existing request card code ... -->
            <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden shadow-xl">
                <div class="p-4 bg-gray-800/50 flex justify-between items-center border-b border-gray-800">
                    <div>
                        <span class="px-2 py-1 bg-blue-600 rounded text-xs font-bold mr-2">{{ $request->method }}</span>
                        <span class="text-lg font-mono tracking-tight text-gray-200">{{ $request->path }}</span>
                    </div>
                    <div class="text-sm text-gray-400">
                        Status: <span class="text-green-400">{{ $request->status }}</span> |
                        Time: {{ $request->duration }}ms
                    </div>
                </div>

                <div class="p-4">
                    <h3 class="text-xs font-semibold uppercase text-gray-500 mb-3 tracking-wider">Database Queries ({{ count($request->queries) }})</h3>
                    <div class="space-y-2">
                        @foreach($request->queries as $query)
                        <div class="bg-black/30 rounded border border-gray-800/50 overflow-hidden mb-3">
                            <div class="p-3">
                                <p class="text-sm font-mono text-blue-300">{{ $query->sql }}</p>
                            </div>

                            <div class="px-3 py-1 bg-gray-900/50 flex justify-between items-center border-t border-gray-800">
                                <!-- Show the File and Line Number -->
                                <span class="text-[10px] text-gray-500 font-mono">
                                    Source: <span class="text-gray-300">{{ $query->file }}:{{ $query->line }}</span>
                                </span>

                                <span class="text-[10px] {{ $query->time > 500 ? 'text-red-400' : 'text-gray-500' }} font-bold uppercase">
                                    {{ $query->time }}ms
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-20 bg-gray-900 rounded-xl border border-dashed border-gray-800">
                <p class="text-gray-500">No requests recorded yet. Hit your API to see data!</p>
            </div>
            @endforelse
        </div>
    </div>
</body>

</html>