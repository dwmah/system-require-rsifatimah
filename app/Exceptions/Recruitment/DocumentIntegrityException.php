<?php

namespace App\Exceptions\Recruitment;

use DomainException;

class DocumentIntegrityException extends DomainException
{
    public function __construct(
        string $message = 'Integritas berkas dokumen persyaratan tidak valid atau file fisik tidak ditemukan.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
