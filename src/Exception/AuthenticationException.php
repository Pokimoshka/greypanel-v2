<?php

declare(strict_types=1);

namespace GreyPanel\Exception;

class AuthenticationException extends \RuntimeException
{
    public function __construct(string $message = 'Authentication failed', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
