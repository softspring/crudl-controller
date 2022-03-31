<?php

namespace Softspring\Component\CrudlController\Form;

use Symfony\Component\HttpFoundation\Request;

interface FormOptionsInterface
{
    public function formOptions($object, Request $request): array;
}