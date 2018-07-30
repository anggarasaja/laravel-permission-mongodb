<?php
// declare(strict_types=1);

namespace Anggarasaja\Permission\Traits;

use Jenssegers\Mongodb\Eloquent\Model;
use Anggarasaja\Permission\PermissionRegistrar;

/**
 * Trait RefreshesPermissionCache
 * @package Anggarasaja\Permission\Traits
 */
trait RefreshesPermissionCache
{
    public static function bootRefreshesPermissionCache()
    {
        static::saved(function () {
            \app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        static::deleted(function () {
            \app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }
}
