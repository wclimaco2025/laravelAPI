<?php

namespace App\Exceptions;

use Exception;

class UserAlreadyExistsException extends Exception
{
    protected $message = 'El usuario con este email ya existe';
    protected $code = 409;
}
