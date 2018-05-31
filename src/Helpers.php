<?php
declare(strict_types=1);

namespace Anggarasaja\Permission;

use Illuminate\Support\Collection;

/**
 * Class Helpers
 * @package Anggarasaja\Permission
 */
class Helpers
{
    /**
     * @param string $guard
     *
     * @return string|null
     */
    public function getModelForGuard($guard)
    {
        return \collect(\config('auth.guards'))
            ->map(function ($guard) {
                return \config("auth.providers.{$guard['provider']}.model");
            })->get($guard);
    }

    /**
     * @param Collection $expected
     * @param string $given
     *
     * @return string
     */
    public function getGuardDoesNotMatchMessage(Collection $expected, $given)
    {
        return "The given role or permission should use guard `{$expected->implode(', ')}` instead of `{$given}`.";
    }

    /**
     * @param string $name
     * @param string $guardName
     *
     * @return string
     */
    public function getPermissionAlreadyExistsMessage($name, $guardName)
    {
        return "A permission `{$name}` already exists for guard `{$guardName}`.";
    }

    /**
     * @param string $name
     * @param string $guardName
     *
     * @return string
     */
    public function getPermissionDoesNotExistMessage($name, $guardName)
    {
        return "There is no permission named `{$name}` for guard `{$guardName}`.";
    }

    /**
     * @param string $name
     * @param string $guardName
     *
     * @return string
     */
    public function getRoleAlreadyExistsMessage($name, $guardName)
    {
        return "A role `{$name}` already exists for guard `{$guardName}`.";
    }

    /**
     * @param string $name
     *
     * @param string $guardName
     *
     * @return string
     */
    public function getRoleDoesNotExistMessage($name, $guardName)
    {
        return "There is no role named `{$name}` for guard `{$guardName}`.";
    }

    /**
     * @param string $roles
     *
     * @return string
     */
    public function getUnauthorizedRoleMessage($roles)
    {
        $message = "User does not have the right roles `{$roles}`.";
        if (! config('permission.display_permission_in_exception')) {
            $message = 'User does not have the right roles.';
        }

        return $message;
    }

    /**
     * @param string $permissions
     *
     * @return string
     */
    public function getUnauthorizedPermissionMessage($permissions)
    {
        $message = "User does not have the right permissions `{$permissions}`.";
        if (! config('permission.display_permission_in_exception')) {
            $message = 'User does not have the right permissions.';
        }

        return $message;
    }

    /**
     * @return string
     */
    public function getUserNotLoggedINMessage()
    {
        return 'User is not logged in.';
    }

    public function isNotLumen()
    {
        return ! stripos(app()->version(), 'lumen');
    }
}
