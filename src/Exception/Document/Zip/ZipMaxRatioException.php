<?php

namespace App\Exception\Document\Zip;

use Symfony\Component\HttpFoundation\Response;

class ZipMaxRatioException extends \Exception
{
    public function __construct(
        string $message = 'The zip file has too much compression',
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
