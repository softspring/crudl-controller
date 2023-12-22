<?php

namespace Softspring\Component\CrudlController\Tests\Event;

use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;
use Symfony\Component\HttpFoundation\Request;

class LoadEntityEventTest extends TestCase
{
    public function testEmpty(): void
    {
        $event = new LoadEntityEvent(null, null);
        $this->assertNull($event->getEntity());
        $this->assertNull($event->getRequest());
    }

    public function testSetValues()
    {
        $entity = new ExampleEntity();
        $request = new Request();
        $event = new LoadEntityEvent($entity, $request);
        $this->assertEquals($entity, $event->getEntity());
        $this->assertEquals($request, $event->getRequest());
        $this->assertFalse($event->isNotFound());
        $event->setNotFound(true);
        $this->assertTrue($event->isNotFound());
    }
}