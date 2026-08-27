<?php

namespace App\Exceptions;

use RuntimeException;

class ApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly string $errorCode,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }
}
