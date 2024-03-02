<?php

namespace App\Exception\Document\Zip;

use Symfony\Component\HttpFoundation\Response;

class ZipMaxSizeException extends \Exception
{
    public function __construct(
        string $message = 'The zip file is too big',
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
