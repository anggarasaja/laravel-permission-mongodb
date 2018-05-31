<?php
declare(strict_types=1);

namespace Anggarasaja\Permission\Contracts;

use Jenssegers\Mongodb\Relations\BelongsToMany;
use Anggarasaja\Permission\Exceptions\PermissionDoesNotExist;

/**
 * Interface PermissionInterface
 * @package Anggarasaja\Permission\Contracts
 */
interface PermissionInterface
{
    /**
     * A permission can be applied to roles.
     * @return BelongsToMany
     */
    public function roles();

    /**
     * Find a permission by its name.
     *
     * @param string $name
     * @param string|null $guardName
     *
     * @throws PermissionDoesNotExist
     *
     * @return PermissionInterface
     */
    public static function findByName($name, $guardName);
}
