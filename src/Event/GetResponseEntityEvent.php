<?php

namespace Softspring\Component\CrudlController\Event;

use Softspring\Component\Events\GetResponseEventInterface;
use Softspring\Component\Events\GetResponseTrait;

class GetResponseEntityEvent extends EntityEvent implements GetResponseEventInterface
{
    use GetResponseTrait;
}
