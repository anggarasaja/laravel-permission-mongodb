<?php
// declare(strict_types=1);

namespace Anggarasaja\Permission\Middlewares;

use Closure;
use Anggarasaja\Permission\Exceptions\UnauthorizedPermission;
use Anggarasaja\Permission\Exceptions\UserNotLoggedIn;
use Anggarasaja\Permission\Helpers;

/**
 * Class PermissionMiddleware
 * @package Anggarasaja\Permission\Middlewares
 */
class PermissionMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @param $permission
     *
     * @return mixed
     * @throws \Anggarasaja\Permission\Exceptions\UnauthorizedException
     */
    public function handle($request, Closure $next, $permission)
    {
        if (app('auth')->guest()) {
            $helpers = new Helpers();
            throw new UserNotLoggedIn(403, $helpers->getUserNotLoggedINMessage());
        }

        $permissions = \is_array($permission) ? $permission : \explode('|', $permission);


        if (! app('auth')->user()->hasAnyPermission($permissions)) {
            $helpers = new Helpers();
            throw new UnauthorizedPermission(
                403,
                $helpers->getUnauthorizedPermissionMessage(implode(', ', $permissions)),
                $permissions
            );
        }

        return $next($request);
    }
}
