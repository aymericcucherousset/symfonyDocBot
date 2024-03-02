<?php

namespace App\Exception\Document\Zip;

use Symfony\Component\HttpFoundation\Response;

class ZipToManyFileException extends \Exception
{
    public function __construct(
        string $message = 'Too many files in the zip',
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
