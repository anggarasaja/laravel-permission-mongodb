<?php

namespace Anggarasaja\Permission\Exceptions;

/**
 * Class UnauthorizedPermission
 * @package Anggarasaja\Permission\Exceptions
 */
class UnauthorizedPermission extends UnauthorizedException
{
    /**
     * UnauthorizedPermission constructor.
     *
     * @param $statusCode
     * @param null $message
     * @param array $requiredPermissions
     */
    public function __construct($statusCode, $message = null, $requiredPermissions = [])
    {
        parent::__construct($statusCode, $message, [], $requiredPermissions);
    }
}
