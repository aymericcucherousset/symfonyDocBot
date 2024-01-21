<?php

namespace App\Exception;

class GithubApiException extends \Exception
{
    public function __construct(string $message = '', int $code = 500, \Throwable $previous = null)
    {
        $message = 'Github API error: '.$message;

        parent::__construct($message, $code, $previous);
    }
}
