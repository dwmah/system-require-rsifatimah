<?php

namespace App\Exceptions\Recruitment;

use DomainException;

class ApplicationAlreadySnapshottedException extends DomainException
{
    public function __construct(
        string $message = 'Data snapshot untuk lamaran ini sudah pernah dibuat dan bersifat permanen (immutable).',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
