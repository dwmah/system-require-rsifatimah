<?php

namespace App\Exceptions\Recruitment;

use DomainException;

class IncompleteProfileException extends DomainException
{
    public function __construct(
        string $message = 'Lengkapi profil dasar Anda sebelum mengajukan lamaran.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
