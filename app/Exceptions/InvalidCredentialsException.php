<?php

namespace App\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    protected $message = 'Credenciales inválidas';
    protected $code = 401;
}
