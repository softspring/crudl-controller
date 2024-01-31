<?php

namespace Softspring\Component\CrudlController\Event;

use Softspring\Component\Events\GetResponseRequestEvent as BaseGetResponseRequestEvent;

class GetResponseRequestEvent extends BaseGetResponseRequestEvent implements GetResponseStatusCodeInterface
{
    use GetResponseStatusCodeTrait;
}
