<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class EntityEvent extends Event
{
    public function __construct(
        protected object $entity,
        protected ?Request $request
    ) {
    }

    public function getEntity(): object
    {
        return $this->entity;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }
}
