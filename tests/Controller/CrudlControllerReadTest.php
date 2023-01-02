<?php

namespace Softspring\Component\CrudlController\Tests\Controller;

use Softspring\Component\CrudlController\Controller\CrudlController;
use Softspring\Component\CrudlController\Exception\EmptyConfigException;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CrudlControllerReadTest extends AbstractCrudlControllerTestCase
{
    public function testReadEmptyConfiguration()
    {
        $controller = new CrudlController($this->manager, $this->dispatcher);

        $this->expectException(EmptyConfigException::class);

        $controller->read(new Request([], [], ['entity' => 'id']));
    }

    public function testReadDenyUnlessGranted()
    {
        $config = [
            'read' => [
                'is_granted' => 'ROLE_MISSING',
            ],
        ];

        $this->expectException(AccessDeniedException::class);

        $controller = $this->getControllerMock($config, ['denyAccessUnlessGranted']);
        $controller->expects($this->once())->method('denyAccessUnlessGranted')->willThrowException(new AccessDeniedException());

        $controller->read(new Request([], [], ['entity' => 'id']));
    }

    public function testReadWithNotFoundEntity()
    {
        $config = [
            'read' => [
                'initialize_event_name' => '',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(null);

        $controller = new CrudlController($this->manager, $this->dispatcher, $config);
        $controller->setContainer($this->container);

        $this->expectException(NotFoundHttpException::class);

        $controller->read(new Request([], [], ['entity' => 'id']));
    }

    public function testReadWithInitializeEventReturningResponse()
    {
        $config = [
            'read' => [
                'initialize_event_name' => 'test_event',
                'view' => 'test_view.html.twig',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        $expectedResponse = new Response();

        $controller = $this->getControllerMock($config, ['dispatchGetResponse']);
        $controller->expects($this->once())->method('dispatchGetResponse')->willReturn($expectedResponse);

        $response = $controller->read(new Request([], [], ['entity' => 'id']));

        $this->assertEquals($expectedResponse, $response);
    }

    public function testReadWithNoSubmittedFormAndViewEvent()
    {
        $config = [
            'read' => [
                'view' => 'test_view.html.twig',
                'view_event_name' => 'test_event',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        // assertion only one dispatch call
        $this->dispatcher->expects($this->once())->method('dispatch');

        $controller = $this->getControllerMock($config, ['renderView']);
        $controller->expects($this->once())->method('renderView')->willReturn($config['read']['view']);

        $response = $controller->read(new Request([], [], ['entity' => 'id']));

        $this->assertEquals($config['read']['view'], $response->getContent());
    }
}
