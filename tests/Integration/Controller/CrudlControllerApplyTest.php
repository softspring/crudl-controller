<?php

namespace Softspring\Component\CrudlController\Tests\Integration\Controller;

use Softspring\Component\Events\GetResponseRequestEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CrudlControllerApplyTest extends AbstractCrudlControllerTestCase
{
    public function testUpdateDenyUnlessGranted(): void
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

    public function testUpdateWithNotFoundEventReturningResponse(): void
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
            if ('not_found_event' === $eventName && $event instanceof GetResponseRequestEvent) {
                $event->setResponse($expectedResponse);
            }

            return $event;
        });

        $controller = $this->createController($configs);
        $response = $controller->apply(new Request(), 'test');
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithNotFoundDefault(): void
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

    public function testUpdateWithInitializeEventReturningResponse(): void
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
            if ('initialize_event' === $eventName && $event instanceof GetResponseRequestEvent) {
                $event->setResponse($expectedResponse);
            }

            return $event;
        });

        $controller = $this->createController($configs);
        $response = $controller->apply(new Request(), 'test');
        $this->assertEquals($expectedResponse, $response);
    }
}
