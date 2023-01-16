<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Component\HttpFoundation\Request;

class FormPrepareEvent extends EntityEvent
{
    protected array $formOptions = [];

    public function __construct(object $entity, ?Request $request, array $formOptions = [])
    {
        parent::__construct($entity, $request);
        $this->formOptions = $formOptions;
    }

    public function getFormOptions(): array
    {
        return $this->formOptions;
    }

    public function setFormOptions(array $formOptions): void
    {
        $this->formOptions = $formOptions;
    }
}