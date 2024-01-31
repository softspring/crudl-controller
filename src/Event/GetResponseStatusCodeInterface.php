<?php

namespace Softspring\Component\CrudlController\Event;

interface GetResponseStatusCodeInterface
{
    public function getStatusCode(): int;

    public function setStatusCode(int $statusCode): void;
}
