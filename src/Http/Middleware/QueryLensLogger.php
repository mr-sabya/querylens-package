<?php

namespace QueryLens\QueryLens\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QueryLensLogger
{
    public function handle($request, Closure $next)
    {
        // --- ADD THIS BLOCK ---
        // If the request is for the QueryLens dashboard or API, skip logging
        if ($request->is('querylens*')) {
            return $next($request);
        }
        // ----------------------

        $requestId = (string) Str::uuid();
        $request->attributes->add(['querylens_id' => $requestId]);

        $startTime = microtime(true);
        $response = $next($request);
        $duration = floor((microtime(true) - $startTime) * 1000);

        try {
            DB::connection('querylens')->table('requests')->insert([
                'id' => $requestId,
                'method' => $request->method(),
                'path' => $request->path(),
                'status' => $response->getStatusCode(),
                'duration' => $duration,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Fail silently
        }

        return $response;
    }
}
