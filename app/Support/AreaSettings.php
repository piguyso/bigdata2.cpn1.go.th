<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AreaSettings
{
    private const DEFAULT_AREA_CODE = '1086010000';

    private const DEFAULT_AREA_NAME = 'สพป.ชุมพร เขต 1';

    private static ?string $code = null;

    private static ?string $name = null;

    public static function code(): string
    {
        if (self::$code !== null) {
            return self::$code;
        }

        $settingCode = self::setting('area_code');

        if ($settingCode !== '') {
            return self::$code = $settingCode;
        }

        $configCode = trim((string) config('services.obec_safety.area_code', self::DEFAULT_AREA_CODE));

        return self::$code = ($configCode !== '' ? $configCode : self::DEFAULT_AREA_CODE);
    }

    public static function name(): string
    {
        if (self::$name !== null) {
            return self::$name;
        }

        $settingName = self::setting('area_name');
        
        if ($settingName === '') {
            $webSubtitle = self::setting('web_subtitle') ?: self::setting('web_name');
            if ($webSubtitle !== '') {
                $settingName = str_replace(['ฐานข้อมูล BigData ', 'BigData '], '', $webSubtitle);
            }
        }

        return self::$name = ($settingName !== '' ? $settingName : self::DEFAULT_AREA_NAME);
    }

    private static function setting(string $key): string
    {
        try {
            if (! Schema::hasTable('settings')) {
                return '';
            }

            return trim((string) DB::table('settings')->where('key', $key)->value('value'));
        } catch (Throwable) {
            return '';
        }
    }
}
