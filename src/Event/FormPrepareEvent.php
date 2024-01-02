<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Component\HttpFoundation\Request;

class FormPrepareEvent extends EntityEvent
{
    public function __construct(
        ?object $entity,
        ?Request $request,
        protected array $formOptions = [],
        protected mixed $type = null,
        protected mixed $data = null,
    ) {
        parent::__construct($entity, $request);
        $this->data = $data ?? $entity;
    }

    public function getFormOptions(): array
    {
        return $this->formOptions;
    }

    public function setFormOptions(array $formOptions): void
    {
        $this->formOptions = $formOptions;
    }

    public function getType(): mixed
    {
        return $this->type;
    }

    public function setType(mixed $type): void
    {
        $this->type = $type;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): void
    {
        $this->data = $data;
    }
}
