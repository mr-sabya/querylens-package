<?php

namespace QueryLens\QueryLens\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;

class QueryLensServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->setupDatabase();
        
        if ($this->app->runningInConsole()) {
            $this->commands([
                \QueryLens\QueryLens\Console\InstallCommand::class,
            ]);
        }

        $this->mergeConfigFrom(__DIR__ . '/../../config/querylens.php', 'querylens');
    }

    public function boot()
    {
        // Allow users to publish the config file using:
        // php artisan vendor:publish --tag=querylens-config
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/querylens.php' => config_path('querylens.php'),
            ], 'querylens-config');
        }

        // 1. Load routes using the URI from the config
        Route::group([
            'prefix' => config('querylens.uri', 'querylens'),
            'middleware' => config('querylens.middleware', ['web']),
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        });

        // 2. Load views
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'querylens');

        // 3. Register Middleware
        $this->app[\Illuminate\Contracts\Http\Kernel::class]->pushMiddleware(\QueryLens\QueryLens\Http\Middleware\QueryLensLogger::class);

        // 4. Create tables if they don't exist
        $this->createTables();

        // 5. Start listening to Database Queries
        $this->listenToQueries();
    }

    protected function setupDatabase()
    {
        $path = storage_path('querylens.sqlite');

        if (!file_exists($path)) {
            touch($path);
        }

        // Force Laravel to recognize this new connection
        Config::set('database.connections.querylens', [
            'driver' => 'sqlite',
            'database' => $path,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
    }

    protected function createTables()
    {
        $schema = Schema::connection('querylens');

        // 1. Requests Table
        if (!$schema->hasTable('requests')) {
            $schema->create('requests', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('method');
                $table->string('path');
                $table->integer('status')->nullable();
                $table->float('duration')->nullable();
                $table->timestamps();
            });
        }

        // 2. Queries Table (UPDATED with file and line)
        if (!$schema->hasTable('queries')) {
            $schema->create('queries', function (Blueprint $table) {
                $table->id();
                $table->uuid('request_id');
                $table->text('sql');
                $table->float('time');
                $table->string('file')->nullable(); // Add this
                $table->integer('line')->nullable(); // Add this
                $table->timestamps();
            });
        }
    }

    protected function listenToQueries()
    {
        DB::listen(function ($query) {
            if ($query->connectionName === 'querylens' || request()->is('querylens*')) {
                return;
            }

            // --- THE PRO PART: BACKTRACE ---
            $trace = collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))
                ->first(function ($frame) {
                    // Find the first file that isn't in the "vendor" folder
                    return isset($frame['file']) && !str_contains($frame['file'], 'vendor');
                });

            DB::connection('querylens')->table('queries')->insert([
                'request_id' => request()->attributes->get('querylens_id', 'console'),
                'sql' => $query->sql,
                'time' => $query->time,
                'file' => $trace ? basename($trace['file']) : 'Unknown', // Show file name
                'line' => $trace ? $trace['line'] : 0,                 // Show line number
                'created_at' => now(),
            ]);
        });
    }
}
