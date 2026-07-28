<?php

declare(strict_types=1);

namespace MCF\Support;

class Path
{
    public static function root(): string
    {
        return config('mcf.paths.root');
    }

    public static function modules(): string
    {
        return config('mcf.paths.modules');
    }

    public static function models(): string
    {
        return config('mcf.paths.database.models');
    }

    public static function migrations(): string
    {
        return config('mcf.paths.database.migrations');
    }

    public static function seeders(): string
    {
        return config('mcf.paths.database.seeders');
    }

    public static function factories(): string
    {
        return config('mcf.paths.database.factories');
    }

    public static function assets(): string
    {
        return config('mcf.paths.assets');
    }

    public static function layouts(): string
    {
        return config('mcf.paths.layouts');
    }

    public static function middleware(): string
    {
        return config('mcf.paths.middleware');
    }

    public static function notifications(): string
    {
        return config('mcf.paths.notifications');
    }

    public static function rules(): string
    {
        return config('mcf.paths.rules');
    }

    public static function stubs(): string
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'stubs';
}

}