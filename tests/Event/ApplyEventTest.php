<?php

namespace Softspring\Component\CrudlController\Tests\Event;

use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Event\ApplyEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class ApplyEventTest extends TestCase
{
    public function testEmptyEvent(): void
    {
        $request = new Request();
        $event = new ApplyEvent(null, $request);
        $this->assertNull($event->getEntity());
        $this->assertNull($event->getForm());
        $this->assertEquals($request, $event->getRequest());
    }

    public function testSetValues(): void
    {
        $entity = new ExampleEntity();
        $request = new Request();
        $form = $this->createMock(FormInterface::class);
        $event = new ApplyEvent($entity, $request, $form);
        $this->assertEquals($entity, $event->getEntity());
        $this->assertEquals($request, $event->getRequest());
        $this->assertEquals($form, $event->getForm());
    }

    public function testApplied(): void
    {
        $entity = new ExampleEntity();
        $request = new Request();
        $form = $this->createMock(FormInterface::class);
        $event = new ApplyEvent($entity, $request, $form);
        $this->assertFalse($event->isApplied());
        $event->setApplied(true);
        $this->assertTrue($event->isApplied());
    }
}