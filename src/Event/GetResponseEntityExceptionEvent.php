<?php

namespace Softspring\Component\CrudlController\Event;

use Softspring\CoreBundle\Event\GetResponseEventInterface;
use Softspring\CoreBundle\Event\GetResponseTrait;
use Symfony\Component\HttpFoundation\Request;

class GetResponseEntityExceptionEvent extends EntityEvent implements GetResponseEventInterface
{
    use GetResponseTrait;

    protected \Throwable $exception;

    public function __construct($entity, ?Request $request, \Throwable $exception)
    {
        parent::__construct($entity, $request);
        $this->exception = $exception;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }
}
