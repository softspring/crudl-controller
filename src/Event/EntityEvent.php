<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

abstract class EntityEvent extends Event implements GetResponseStatusCodeInterface
{
    use GetResponseStatusCodeTrait;

    public function __construct(
        protected mixed $entity,
        protected ?Request $request
    ) {
    }

    public function getEntity(): mixed
    {
        return $this->entity;
    }

    public function setEntity(mixed $entity): void
    {
        $this->entity = $entity;
    }

    public function getRequest(): ?Request
    {
        return $this->request;
    }
}
