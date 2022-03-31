<?php

namespace Softspring\Component\CrudlController\Tests\Event;

use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Event\EntityEvent;
use Symfony\Component\HttpFoundation\Request;

class EntityEventTest extends TestCase
{
    public function testGetRequest()
    {
        $entity = new ExampleEntity();
        $request = new Request();
        $event = new EntityEvent($entity, $request);
        $this->assertEquals($request, $event->getRequest());
    }

    public function testGetEntity()
    {
        $entity = new ExampleEntity();
        $request = new Request();
        $event = new EntityEvent($entity, $request);
        $this->assertEquals($entity, $event->getEntity());
    }
}
