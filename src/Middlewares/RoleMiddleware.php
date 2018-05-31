<?php
declare(strict_types=1);

namespace Anggarasaja\Permission\Middlewares;

use Closure;
use Anggarasaja\Permission\Exceptions\UnauthorizedRole;
use Anggarasaja\Permission\Exceptions\UserNotLoggedIn;
use Anggarasaja\Permission\Helpers;

/**
 * Class RoleMiddleware
 * @package Anggarasaja\Permission\Middlewares
 */
class RoleMiddleware
{
    /**
     * @param $request
     * @param Closure $next
     * @param $role
     *
     * @return mixed
     * @throws \Anggarasaja\Permission\Exceptions\UnauthorizedException
     */
    public function handle($request, Closure $next, $role)
    {
        if (app('auth')->guest()) {
            $helpers = new Helpers();
            throw new UserNotLoggedIn(403, $helpers->getUserNotLoggedINMessage());
        }

        $roles = \is_array($role) ? $role : \explode('|', $role);

        if (! app('auth')->user()->hasAnyRole($roles)) {
            $helpers = new Helpers();
            throw new UnauthorizedRole(403, $helpers->getUnauthorizedRoleMessage(implode(', ', $roles)), $roles);
        }

        return $next($request);
    }
}
