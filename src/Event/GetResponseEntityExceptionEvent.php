<?php

namespace Softspring\Component\CrudlController\Event;

use Softspring\Component\Events\GetResponseEventInterface;
use Softspring\Component\Events\GetResponseTrait;
use Symfony\Component\HttpFoundation\Request;

class GetResponseEntityExceptionEvent extends EntityEvent implements GetResponseEventInterface
{
    use GetResponseTrait;

    public function __construct(
        $entity,
        ?Request $request,
        protected \Throwable $exception
    ) {
        parent::__construct($entity, $request);
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
