<?php

namespace App\Exception\Document\Zip;

use Symfony\Component\HttpFoundation\Response;

class ZipNavigationProtectionException extends \Exception
{
    public function __construct(
        string $message = 'The zip file contains navigation payloads',
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
