<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class FileLoadException extends Exception
{
    public function __construct(string $file = '', int $code = 500, ?Throwable $previous = null)
    {
        parent::__construct("Failed to load file : ${$file}", $code, $previous);
    }
}
