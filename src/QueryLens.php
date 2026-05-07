<?php
namespace QueryLens\QueryLens;

use Illuminate\Support\Facades\DB;

class QueryLens
{
    public static function recordRequest($data)
    {
        if (!config('querylens.enabled')) return;

        DB::connection('querylens')->table('requests')->insert($data);
    }

    public static function recordQuery($data)
    {
        if (!config('querylens.enabled')) return;

        DB::connection('querylens')->table('queries')->insert($data);
    }
}