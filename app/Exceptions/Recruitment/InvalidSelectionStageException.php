<?php

namespace App\Exceptions\Recruitment;

use InvalidArgumentException;

class InvalidSelectionStageException extends InvalidArgumentException
{
    public function __construct(
        string $message = 'Tahapan seleksi yang dievaluasi tidak termasuk dalam tahapan lowongan pekerjaan yang dilamar.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
