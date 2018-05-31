<?php
declare(strict_types=1);

namespace Anggarasaja\Permission\Contracts;

use Jenssegers\Mongodb\Relations\BelongsToMany;
use Anggarasaja\Permission\Exceptions\RoleDoesNotExist;

/**
 * Interface RoleInterface
 * @package Anggarasaja\Permission\Contracts
 */
interface RoleInterface
{
    /**
     * A role may be given various permissions.
     * @return BelongsToMany
     */
    public function permissions();

    /**
     * Find a role by its name and guard name.
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @return RoleInterface
     *
     * @throws RoleDoesNotExist
     */
    public static function findByName($name, $guardName);

    /**
     * Determine if the user may perform the given permission.
     *
     * @param string|PermissionInterface $permission
     *
     * @return bool
     */
    public function hasPermissionTo($permission);
}
