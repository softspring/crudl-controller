<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Component\HttpFoundation\Request;

class FormPrepareEvent extends EntityEvent
{
    public function __construct(
        object $entity,
        ?Request $request,
        protected array $formOptions = []
    ) {
        parent::__construct($entity, $request);
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
