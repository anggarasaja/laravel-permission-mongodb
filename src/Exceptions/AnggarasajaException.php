<?php

namespace Anggarasaja\Permission\Exceptions;

use InvalidArgumentException;
use Throwable;

/**
 * Class AnggarasajaException
 * @package Anggarasaja\Permission\Exceptions
 */
class AnggarasajaException extends InvalidArgumentException
{
    /**
     * AnggarasajaException constructor.
     *
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        if (\config('permission.log_registration_exception')) {
            $logger = \app('log');
            $logger->alert($message);
        }
    }
}
