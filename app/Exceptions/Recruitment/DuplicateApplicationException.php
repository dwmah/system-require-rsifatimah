<?php

namespace App\Exceptions\Recruitment;

use DomainException;

class DuplicateApplicationException extends DomainException
{
    public function __construct(
        string $message = 'Anda sudah memiliki lamaran pada periode rekrutmen ini.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
