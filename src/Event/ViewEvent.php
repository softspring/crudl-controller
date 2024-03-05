<?php

namespace Softspring\Component\CrudlController\Event;

use Softspring\Component\Events\ViewEvent as BaseViewEvent;
use Symfony\Component\HttpFoundation\Request;

class ViewEvent extends BaseViewEvent implements GetResponseStatusCodeInterface
{
    use GetResponseStatusCodeTrait;

    public function __construct($data, protected ?string $template, ?Request $request = null)
    {
        parent::__construct($data, $request);
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }
}
