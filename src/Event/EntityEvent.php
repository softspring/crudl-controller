<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class EntityEvent extends Event
{
    protected object $entity;

    protected ?Request $request;

    /**
     * AccountEvent constructor.
     */
    public function __construct(object $entity, ?Request $request)
    {
        $this->entity = $entity;
        $this->request = $request;
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
