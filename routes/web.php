<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Dashboard View - Change 'querylens' to '/'
Route::get('/', function () {
    $requests = DB::connection('querylens')->table('requests')->latest()->get();

    foreach ($requests as $request) {
        $request->queries = DB::connection('querylens')
            ->table('queries')
            ->where('request_id', $request->id)
            ->get();
    }

    return view('querylens::dashboard', compact('requests'));
});

// Clear Logs Action - Change 'querylens/clear' to 'clear'
Route::post('clear', function () {
    DB::connection('querylens')->table('queries')->truncate();
    DB::connection('querylens')->table('requests')->delete();

    // Use config to redirect back to the correct URI
    return redirect(config('querylens.uri', 'querylens'));
});