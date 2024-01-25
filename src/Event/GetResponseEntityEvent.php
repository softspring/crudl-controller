<?php

namespace Softspring\Component\CrudlController\Event;

use Softspring\Component\Events\GetResponseEventInterface;
use Softspring\Component\Events\GetResponseTrait;

/**
 * @internal this class is internal, should not be used outside the component
 */
class GetResponseEntityEvent extends EntityEvent implements GetResponseEventInterface
{
    use GetResponseTrait;
}
