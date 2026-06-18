<?php

declare(strict_types=1);

namespace Softspring\Component\CrudlController\Tests\Unit\Event;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Event\ExceptionEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class EventAccessorsTest extends TestCase
{
    public function testFormPrepareEventDefaultsDataToEntityAndAllowsMutation(): void
    {
        $entity = new ExampleEntity();
        $request = new Request();

        $event = new FormPrepareEvent($entity, $request, ['validation_groups' => ['create']], 'app.form');

        self::assertSame($entity, $event->getEntity());
        self::assertSame($request, $event->getRequest());
        self::assertSame($entity, $event->getData());
        self::assertSame(['validation_groups' => ['create']], $event->getFormOptions());
        self::assertSame('app.form', $event->getType());

        $replacement = new ExampleEntity();
        $event->setEntity($replacement);
        $event->setData('payload');
        $event->setType('app.other_form');
        $event->setFormOptions(['csrf_protection' => false]);

        self::assertSame($replacement, $event->getEntity());
        self::assertSame('payload', $event->getData());
        self::assertSame('app.other_form', $event->getType());
        self::assertSame(['csrf_protection' => false], $event->getFormOptions());
    }

    public function testFailureEventExposesExceptionAndForm(): void
    {
        $exception = new LogicException('Unable to save entity');
        $form = $this->createMock(FormInterface::class);
        $event = new FailureEvent(new ExampleEntity(), new Request(), $exception, $form);

        self::assertSame($exception, $event->getException());
        self::assertSame($form, $event->getForm());
    }

    public function testExceptionEventExposesRequestExceptionAndStatusCode(): void
    {
        $request = new Request();
        $exception = new LogicException('Broken action');
        $event = new ExceptionEvent($request, $exception);

        self::assertSame($request, $event->getRequest());
        self::assertSame($exception, $event->getException());
        self::assertSame(Response::HTTP_OK, $event->getStatusCode());

        $event->setStatusCode(Response::HTTP_BAD_REQUEST);

        self::assertSame(Response::HTTP_BAD_REQUEST, $event->getStatusCode());
    }

    public function testStatusCodeRejectsInvalidValues(): void
    {
        $event = new ExceptionEvent(new Request(), new LogicException());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HTTP status code "99"');

        $event->setStatusCode(99);
    }

    public function testViewEventExposesTemplateAndStatusCode(): void
    {
        $event = new ViewEvent(['entity' => new ExampleEntity()], 'read.html.twig', new Request());

        self::assertSame('read.html.twig', $event->getTemplate());
        self::assertSame(Response::HTTP_OK, $event->getStatusCode());

        $event->setTemplate('custom.html.twig');
        $event->setStatusCode(Response::HTTP_CREATED);

        self::assertSame('custom.html.twig', $event->getTemplate());
        self::assertSame(Response::HTTP_CREATED, $event->getStatusCode());
    }
}
