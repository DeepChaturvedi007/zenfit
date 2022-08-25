<?php

namespace AppBundle\Services;

class ErrorHandlerService
{
    public function __construct(string $sentryDSN, private string $env)
    {
        if ($env === 'prod') {
            \Sentry\init(['dsn' => $sentryDSN]);
        }
    }

    public function captureException(\Throwable $e): void
    {
        if ($this->env !== 'prod') {
            return;
        }

        \Sentry\captureException($e);
    }
}
