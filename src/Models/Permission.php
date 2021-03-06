<?php
// declare(strict_types=1);

namespace Anggarasaja\Permission\Models;

use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Relations\BelongsToMany;
use Anggarasaja\Permission\Contracts\PermissionInterface;
use Anggarasaja\Permission\Exceptions\PermissionAlreadyExists;
use Anggarasaja\Permission\Exceptions\PermissionDoesNotExist;
use Anggarasaja\Permission\Guard;
use Anggarasaja\Permission\Helpers;
use Anggarasaja\Permission\PermissionRegistrar;
use Anggarasaja\Permission\Traits\HasRoles;
use Anggarasaja\Permission\Traits\RefreshesPermissionCache;

/**
 * Class Permission
 * @package Anggarasaja\Permission\Models
 */
class Permission extends Model implements PermissionInterface
{
    use HasRoles;
    use RefreshesPermissionCache;

    public $guarded = ['id'];
    protected $helpers;

    /**
     * Permission constructor.
     *
     * @param array $attributes
     *
     * @throws \ReflectionException
     */
    public function __construct(array $attributes = [])
    {
        if (!empty($attributes['guard_name'])) $name = $attributes['guard_name'];
        else $name = (new Guard())->getDefaultName(static::class);

        $attributes['guard_name'] = $name;
        // dd($attributes['guard_name']);
        parent::__construct($attributes);

        $this->helpers = new Helpers();

        $this->setTable(\config('permission.collection_names.permissions'));
    }

    /**
     * Create new Permission
     *
     * @param array $attributes
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     * @throws \Anggarasaja\Permission\Exceptions\PermissionAlreadyExists
     * @throws \ReflectionException
     */
    public static function create(array $attributes = [])
    {
        $helpers                  = new Helpers();

        if (!empty($attributes['guard_name'])) $name = $attributes['guard_name'];
        else $name = (new Guard())->getDefaultName(static::class);

        $attributes['guard_name'] = $name;

        if (static::getPermissions()->where('name', $attributes['name'])->where(
            'guard_name',
            $attributes['guard_name']
        )->first()) {
            $name      = (string) $attributes['name'];
            $guardName = (string) $attributes['guard_name'];
            throw new PermissionAlreadyExists($helpers->getPermissionAlreadyExistsMessage($name, $guardName));
        }

        if ($helpers->isNotLumen() && app()->version() < '5.4') {
            return parent::create($attributes);
        }

        return static::query()->create($attributes);
    }

    /**
     * Find or create permission by its name (and optionally guardName).
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @return PermissionInterface
     * @throws \Anggarasaja\Permission\Exceptions\PermissionAlreadyExists
     * @throws \ReflectionException
     */
    public static function findOrCreate($name, $guardName = null)
    {
        if (!empty($guardName)) $name = $guardName;
        else $name = (new Guard())->getDefaultName(static::class);

        $guardName = $name;

        $permission = static::getPermissions()
                            ->where('name', $name)
                            ->where('guard_name', $guardName)
                            ->first();

        if (! $permission) {
            $permission = static::create(['name' => $name, 'guard_name' => $guardName]);
        }

        return $permission;
    }

    /**
     * A permission can be applied to roles.
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            \config('permission.models.role'),
            \config('permission.collection_names.role_has_permissions')
        );
    }

    /**
     * A permission belongs to some users of the model associated with its guard.
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany($this->helpers->getModelForGuard($this->attributes['guard_name']));
    }

    /**
     * Find a permission by its name (and optionally guardName).
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @return PermissionInterface
     * @throws PermissionDoesNotExist
     * @throws \ReflectionException
     */
    public static function findByName($name, $guardName = null)
    {
        if (!empty($guardName)) $name2 = $guardName;
        else $name2 = (new Guard())->getDefaultName(static::class);

        $guardName = $name2;

        $permission = static::getPermissions()->where('name', $name)->where('guard_name', $guardName)->first();

        if (! $permission) {
            $helpers = new Helpers();
            throw new PermissionDoesNotExist($helpers->getPermissionDoesNotExistMessage($name, $guardName));
        }

        return $permission;
    }

    /**
     * Get the current cached permissions.
     * @return Collection
     */
    protected static function getPermissions()
    {
        return \app(PermissionRegistrar::class)->getPermissions();
    }
}
