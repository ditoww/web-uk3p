<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Setting
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $v = DB::table('site_settings')->where('key', $key)->value('value');
        return ($v === null || $v === '') ? $default : $v;
    }

    public static function put(string $key, ?string $value): void
    {
        DB::table('site_settings')->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now()]
        );
    }
}