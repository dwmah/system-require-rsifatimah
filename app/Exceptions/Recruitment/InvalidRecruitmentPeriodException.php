<?php

namespace App\Exceptions\Recruitment;

use InvalidArgumentException;

class InvalidRecruitmentPeriodException extends InvalidArgumentException
{
    public function __construct(
        string $message = 'Periode rekrutmen pada lamaran tidak sesuai dengan periode lowongan pekerjaan yang dilamar.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
