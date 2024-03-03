<?php

namespace App\Exception\Repository;

use Symfony\Component\HttpFoundation\Response;

class BranchNotFoundException extends \Exception
{
    public function __construct(
        string $branch,
        int $code = Response::HTTP_NOT_FOUND,
        ?\Exception $previous = null
    ) {
        parent::__construct(
            sprintf('Branch %s not found', $branch),
            $code,
            $previous
        );
    }
}
