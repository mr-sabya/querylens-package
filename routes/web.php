<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Dashboard View
Route::get('querylens', function () {
    $requests = DB::connection('querylens')->table('requests')->latest()->get();

    foreach ($requests as $request) {
        $request->queries = DB::connection('querylens')
            ->table('queries')
            ->where('request_id', $request->id)
            ->get();
    }

    return view('querylens::dashboard', compact('requests'));
});

// Clear Logs Action
Route::post('querylens/clear', function () {
    DB::connection('querylens')->table('queries')->truncate();
    DB::connection('querylens')->table('requests')->delete();

    return redirect('querylens');
});
