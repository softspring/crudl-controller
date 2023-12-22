<?php

namespace Softspring\Component\CrudlController\Event;

use Softspring\Component\Events\GetResponseEventInterface;
use Softspring\Component\Events\GetResponseTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class FailureEvent extends EntityEvent implements GetResponseEventInterface
{
    use GetResponseTrait;

    public function __construct(
        $entity,
        ?Request $request,
        protected \Throwable $exception,
        protected ?FormInterface $form = null,
    ) {
        parent::__construct($entity, $request);
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function getForm(): ?FormInterface
    {
        return $this->form;
    }
}
