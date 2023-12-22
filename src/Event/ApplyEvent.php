<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ApplyEvent extends EntityEvent
{
    public function __construct(
        ?object $entity,
        ?Request $request,
        protected ?FormInterface $form = null
    ) {
        parent::__construct($entity, $request);
    }

    protected bool $applied = false;

    public function isApplied(): bool
    {
        return $this->applied;
    }

    public function setApplied(bool $applied): void
    {
        $this->applied = $applied;
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }
}
