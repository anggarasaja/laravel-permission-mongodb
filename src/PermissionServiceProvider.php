<?php

namespace Anggarasaja\Permission;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Anggarasaja\Permission\Contracts\PermissionInterface as Permission;
use Anggarasaja\Permission\Contracts\RoleInterface as Role;
use Anggarasaja\Permission\Directives\PermissionDirectives;

/**
 * Class PermissionServiceProvider
 * @package Anggarasaja\Permission
 */
class PermissionServiceProvider extends ServiceProvider
{
    public function boot(PermissionRegistrar $permissionLoader)
    {
        $helpers = new Helpers();
        if ($helpers->isNotLumen()) {
            $this->publishes([
                __DIR__ . '/../config/permission.php' => $this->app->configPath() . '/permission.php',
            ], 'config');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\CreateRole::class,
                Commands\CreatePermission::class,
            ]);
        }

        $this->registerModelBindings();

        $permissionLoader->registerPermissions();
    }

    public function register()
    {
        $helpers = new Helpers();
        if ($helpers->isNotLumen()) {
            $this->mergeConfigFrom(
                __DIR__ . '/../config/permission.php',
                'permission'
            );
        }

        $this->registerBladeExtensions();
    }

    protected function registerModelBindings()
    {
        $config = $this->app->config['permission.models'];

        $this->app->bind(Permission::class, $config['permission']);
        $this->app->bind(Role::class, $config['role']);
    }

    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $permissionDirectives = new PermissionDirectives($bladeCompiler);

            $permissionDirectives->roleDirective();
            $permissionDirectives->hasroleDirective();
            $permissionDirectives->hasanyroleDirective();
            $permissionDirectives->hasallrolesDirective();
        });
    }
}
