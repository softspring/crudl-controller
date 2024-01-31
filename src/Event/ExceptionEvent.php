<?php

namespace Softspring\Component\CrudlController\Event;

use Softspring\Component\Events\GetResponseEventInterface;
use Softspring\Component\Events\GetResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class ExceptionEvent extends Event implements GetResponseEventInterface, GetResponseStatusCodeInterface
{
    use GetResponseTrait;
    use GetResponseStatusCodeTrait;

    public function __construct(
        protected ?Request $request,
        protected \Throwable $exception
    ) {
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
