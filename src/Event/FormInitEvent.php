<?php

declare(strict_types=1);

namespace Softspring\Component\CrudlController\Event;

use Softspring\Component\Events\FormEvent;

class FormInitEvent extends FormEvent implements GetResponseStatusCodeInterface
{
    use GetResponseStatusCodeTrait;
    use CrudlEventTrait;
}
