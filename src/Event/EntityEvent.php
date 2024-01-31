<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

abstract class EntityEvent extends Event implements GetResponseStatusCodeInterface
{
    use GetResponseStatusCodeTrait;

    public function __construct(
        protected ?object $entity,
        protected ?Request $request
    ) {
    }

    public function getEntity(): ?object
    {
        return $this->entity;
    }

    public function setEntity(?object $entity): void
    {
        $this->entity = $entity;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }
}
