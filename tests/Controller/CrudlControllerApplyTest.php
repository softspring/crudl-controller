<?php

namespace Softspring\Component\CrudlController\Tests\Controller;

use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\Component\CrudlController\Event\GetResponseFormEvent;
use Softspring\Component\CrudlController\Tests\Controller\Example\UpdateForm;
use Softspring\Component\Events\GetResponseRequestEvent;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CrudlControllerApplyTest extends AbstractCrudlControllerTestCase
{
    public function testUpdateDenyUnlessGranted()
    {
        $configs = [
            'test' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'is_granted' => 'ROLE_MISSING',
            ],
        ];

        $this->expectException(AccessDeniedException::class);

        $controller = $this->createController($configs);
        $controller->apply(new Request(), 'test');
    }

    public function testUpdateWithNotFoundEventReturningResponse()
    {
        $configs = [
            'test' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'is_granted' => null,
                'not_found_event_name' => 'not_found_event',
            ],
        ];

        $expectedResponse = new Response();

        $this->dispatcher->expects($this->once())->method('dispatch')->willReturnCallback(function ($event, string $eventName) use ($expectedResponse) {
            $eventName == 'not_found_event' && $event instanceof GetResponseRequestEvent  && $event->setResponse($expectedResponse);

            return $event;
        });

        $controller = $this->createController($configs);
        $response = $controller->apply(new Request(), 'test');
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithNotFoundDefault()
    {
        $configs = [
            'test' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'is_granted' => null,
                'not_found_event_name' => null,
            ],
        ];

        $this->expectException(NotFoundHttpException::class);
        $controller = $this->createController($configs);
        $controller->apply(new Request(), 'test');
    }

    public function testUpdateWithInitializeEventReturningResponse()
    {
        $configs = [
            'test' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => 'initialize_event',
            ],
        ];

        $expectedResponse = new Response();
        $this->dispatcher->expects($this->once())->method('dispatch')->willReturnCallback(function ($event, string $eventName) use ($expectedResponse) {
            $eventName == 'initialize_event' && $event instanceof GetResponseRequestEvent && $event->setResponse($expectedResponse);

            return $event;
        });

        $controller = $this->createController($configs);
        $response = $controller->apply(new Request(), 'test');
        $this->assertEquals($expectedResponse, $response);
    }
}
