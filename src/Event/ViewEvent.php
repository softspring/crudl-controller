<?php

namespace Softspring\Component\CrudlController\Event;

use ArrayObject;
use Softspring\Component\Events\ViewEvent as BaseViewEvent;
use Symfony\Component\HttpFoundation\Request;

class ViewEvent extends BaseViewEvent implements GetResponseStatusCodeInterface
{
    use GetResponseStatusCodeTrait;
    use CrudlEventTrait;

    public function __construct(array|ArrayObject $data, protected ?string $template, ?Request $request = null)
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
