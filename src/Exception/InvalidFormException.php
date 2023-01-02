<?php

namespace Softspring\Component\CrudlController\Exception;

use Symfony\Component\Form\FormTypeInterface;

class InvalidFormException extends \InvalidArgumentException
{
    public function __construct(string $action, string $formClassName = FormTypeInterface::class, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct(sprintf(ucfirst($action).' form must be an instance of %s or a class name', $formClassName), $code, $previous);
    }
}
