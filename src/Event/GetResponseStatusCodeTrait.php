<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Component\HttpFoundation\Response;

trait GetResponseStatusCodeTrait
{
    protected int $statusCode = Response::HTTP_OK;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): void
    {
        if ($statusCode < 100 || $statusCode > 599) {
            throw new \InvalidArgumentException(sprintf('Invalid HTTP status code "%s"', $statusCode));
        }

        $this->statusCode = $statusCode;
    }
}
