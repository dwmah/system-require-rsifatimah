<?php

namespace App\Exceptions\Recruitment;

use DomainException;

class InactiveVacancyException extends DomainException
{
    public function __construct(
        string $message = 'Lowongan pekerjaan ini sedang tidak dibuka untuk pendaftaran.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
