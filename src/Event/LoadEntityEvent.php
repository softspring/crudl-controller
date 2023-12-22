<?php

namespace Softspring\Component\CrudlController\Event;

class LoadEntityEvent extends EntityEvent
{
    protected bool $notFound = false;

    public function isNotFound(): bool
    {
        return $this->notFound;
    }

    public function setNotFound(bool $notFound): void
    {
        $this->notFound = $notFound;
    }
}
