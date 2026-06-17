<?php

namespace App\Exceptions;

class ForbiddenException extends BaseException
{
    public function __construct(string $message = 'Forbidden')
    {
        parent::__construct($message, 403);
    }
}
