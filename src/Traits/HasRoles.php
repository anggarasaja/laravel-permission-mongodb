<?php
// declare(strict_types=1);

namespace Anggarasaja\Permission\Traits;

use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Relations\BelongsToMany;
use Anggarasaja\Permission\Contracts\PermissionInterface as Permission;
use Anggarasaja\Permission\Contracts\RoleInterface as Role;
use ReflectionException;

/**
 * Trait HasRoles
 * @package Maklad\Permission\Traits
 */
trait HasRoles
{
    use HasPermissions;

    public static function bootHasRoles()
    {
        static::deleting(function (Model $model) {
            if (isset($model->forceDeleting) && !$model->forceDeleting) {
                return;
            }

            $model->roles()->sync([]);
        });
    }

    /**
     * A model may have multiple roles.
     */
    public function roles()
    {
        return $this->belongsToMany(\config('permission.models.role'))->withTimestamps();
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param Builder $query
     * @param string|array|Role|Collection $roles
     *
     * @return Builder
     */
    public function scopeRole(Builder $query, $roles)
    {
        $roles = $this->convertToRoleModels($roles);

        return $query->whereIn('role_ids', $roles->pluck('_id'));
    }

    /**
     * Assign the given role to the model.
     *
     * @param array|string|Role $roles
     *
     * @return array|Role|string
     */
    public function assignRole($roles)
    {
        $roles = \collect($roles)
            ->flatten()
            ->map(function ($role) {
                return $this->getStoredRole($role);
            })
            ->each(function ($role) {
                $this->ensureModelSharesGuard($role);
            })
            ->all();

        $this->roles()->saveMany($roles);

        $this->forgetCachedPermissions();

        return $roles;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param array|string|Role $roles
     *
     * @return array|Role|string
     */
    public function removeRole($roles)
    {
        \collect($roles)
            ->flatten()
            ->map(function ($role) {
                $role = $this->getStoredRole($role);
                $this->roles()->detach($role);

                return $role;
            });

        $this->forgetCachedPermissions();

        return $roles;
    }

    /**
     * Remove all current roles and set the given ones.
     *
     * @param array $roles
     *
     * @return $this
     */
    public function syncRoles($roles)
    {
        $this->roles()->sync([]);

        return $this->assignRole($roles);
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param string|array|Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasRole($roles)
    {
        if (\is_string($roles) && false !== \strpos($roles, '|')) {
            $roles = \explode('|', $roles);
        }

        if (\is_string($roles) || $roles instanceof Role) {
            if (!empty($roles->name)) $role_name = $roles->name;
            else $role_name = $roles;
            return $this->roles->contains('name', $role_name);
            // return $this->roles->contains('name', $roles->name ?? $roles);
        }

        $roles = \collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return ! $roles->intersect($this->roles->pluck('name'))->isEmpty();
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * @param string|array|Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAnyRole($roles)
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param string|Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAllRoles($roles)
    {
        if (\is_string($roles) && false !== strpos($roles, '|')) {
            $roles = \explode('|', $roles);
        }

        if (\is_string($roles) || $roles instanceof Role) {
            return $this->hasRole($roles);
        }

        $roles = \collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect($this->roles->pluck('name')) == $roles;
    }

    /**
     * Return Role object
     *
     * @param String|Role $role role name
     *
     * @return Role
     * @throws ReflectionException
     */
    protected function getStoredRole($role)
    {
        if (\is_string($role)) {
            return \app(Role::class)->findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    /**
     * Return a collection of role names associated with this user.
     *
     * @return Collection
     */
    public function getRoleNames()
    {
        return $this->roles()->pluck('name');
    }

    /**
     * Convert to Role Models
     *
     * @param $roles
     *
     * @return Collection
     */
    private function convertToRoleModels($roles)
    {
        if (is_array($roles)) {
            $roles = collect($roles);
        }

        if (!$roles instanceof Collection) {
            $roles = collect([$roles]);
        }

        $roles = $roles->map(function ($role) {
            return $this->getStoredRole($role);
        });

        return $roles;
    }
}
