<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class FileExtensionException extends Exception
{
    public function __construct(string $extension = '', int $code = 415, ?Throwable $previous = null)
    {
        parent::__construct("Unsupported file extension : ${$extension}", $code, $previous);
    }
}
