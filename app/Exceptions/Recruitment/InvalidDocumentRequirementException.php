<?php

namespace App\Exceptions\Recruitment;

use InvalidArgumentException;

class InvalidDocumentRequirementException extends InvalidArgumentException
{
    public function __construct(
        string $message = 'Persyaratan dokumen yang diunggah tidak termasuk dalam persyaratan lowongan pekerjaan yang dilamar.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
