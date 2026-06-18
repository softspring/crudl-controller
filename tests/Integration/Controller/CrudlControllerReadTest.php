<?php

namespace Softspring\Component\CrudlController\Tests\Integration\Controller;

use Softspring\Component\Events\GetResponseRequestEvent;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CrudlControllerReadTest extends AbstractCrudlControllerTestCase
{
    public function testReadDenyUnlessGranted(): void
    {
        $configs = [
            'read' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'is_granted' => 'ROLE_MISSING',
            ],
        ];

        $this->expectException(AccessDeniedException::class);

        $controller = $this->createController($configs);
        $controller->read(new Request());
    }

    public function testReadWithNotFoundEventReturningResponse(): void
    {
        $configs = [
            'read' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
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
        $response = $controller->read(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testReadWithNotFoundDefault(): void
    {
        $configs = [
            'read' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'is_granted' => null,
                'not_found_event_name' => null,
            ],
        ];

        $this->expectException(NotFoundHttpException::class);
        $controller = $this->createController($configs);
        $controller->read(new Request());
    }

    public function testReadWithInitializeEventReturningResponse(): void
    {
        $configs = [
            'read' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => 'initialize_event',
                'view_event_name' => null,
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
        $response = $controller->read(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testReadWithNoSubmittedFormAndViewEvent(): void
    {
        $config = [
            'read' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => null,
                'view_event_name' => 'view_event',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(new stdClass());

        $this->twig->expects($this->once())->method('render')->willReturn($config['read']['view']);

        $controller = $this->createController($config);
        $response = $controller->read(new Request());
        $this->assertEquals($config['read']['view'], $response->getContent());
    }
}
