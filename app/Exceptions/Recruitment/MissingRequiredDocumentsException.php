<?php

namespace App\Exceptions\Recruitment;

use DomainException;
use Illuminate\Support\Collection;

class MissingRequiredDocumentsException extends DomainException
{
    /**
     * @param  Collection<int, string>  $missingDocuments
     */
    public function __construct(
        protected Collection $missingDocuments,
        string $message = 'Masih terdapat dokumen wajib yang belum diunggah.',
        int $code = 422,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return Collection<int, string>
     */
    public function getMissingDocuments(): Collection
    {
        return $this->missingDocuments;
    }
}
