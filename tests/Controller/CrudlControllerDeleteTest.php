<?php

namespace Softspring\Component\CrudlController\Tests\Controller;

use Softspring\Component\CrudlController\Controller\CrudlController;
use Softspring\Component\CrudlController\Exception\EmptyConfigException;
use Softspring\Component\CrudlController\Tests\Controller\Example\DeleteForm;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CrudlControllerDeleteTest extends AbstractCrudlControllerTestCase
{
    public function testDeleteEmptyConfiguration()
    {
        $controller = new CrudlController($this->manager, $this->dispatcher);

        $this->expectException(EmptyConfigException::class);

        $controller->delete(new Request([], [], ['entity' => 'id']));
    }

    public function testDeleteDenyUnlessGranted()
    {
        $config = [
            'delete' => [
                'is_granted' => 'ROLE_MISSING',
            ],
        ];

        $this->expectException(AccessDeniedException::class);

        $controller = $this->getControllerMock($config, ['denyAccessUnlessGranted']);
        $controller->expects($this->once())->method('denyAccessUnlessGranted')->willThrowException(new AccessDeniedException());
        $controller->delete(new Request([], [], ['entity' => 'id']));
    }

    public function testDeleteWithNotFoundEntity()
    {
        $config = [
            'delete' => [
                'initialize_event_name' => '',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(null);

        $controller = new CrudlController($this->manager, $this->dispatcher, $config);

        $this->expectException(NotFoundHttpException::class);

        $controller->delete(new Request([], [], ['entity' => 'id']));
    }

    public function testDeleteWithInitializeEventReturningResponse()
    {
        $config = [
            'delete' => [
                'initialize_event_name' => 'test_event',
                'view' => 'test_view.html.twig',
                'form' => $this->getMockBuilder(DeleteForm::class)->getMock(),
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        $expectedResponse = new Response();

        $controller = $this->getControllerMock($config, ['dispatchGetResponse']);
        $controller->expects($this->once())->method('dispatchGetResponse')->willReturn($expectedResponse);

        $response = $controller->delete(new Request([], [], ['entity' => 'id']));

        $this->assertEquals($expectedResponse, $response);
    }

    public function testDeleteWithNoSubmittedFormAndViewEvent()
    {
        $config = [
            'delete' => [
                'view' => 'test_view.html.twig',
                'view_event_name' => 'test_event',
                'form' => $this->getMockBuilder(DeleteForm::class)->getMock(),
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        // assertion only one dispatch call
        $this->dispatcher->expects($this->once())->method('dispatch');

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);

        $controller = $this->getControllerMock($config, ['renderView']);
        $controller->expects($this->once())->method('renderView')->willReturn($config['delete']['view']);

        $response = $controller->delete(new Request([], [], ['entity' => 'id']));

        $this->assertEquals($config['delete']['view'], $response->getContent());
    }

    public function testDeleteWithFormSubmittedAndInvalidReceivingEventResponse()
    {
        $config = [
            'delete' => [
                'form_invalid_event_name' => 'test_event',
                'form' => $this->getMockBuilder(DeleteForm::class)->getMock(),
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(false);

        $controller = $this->getControllerMock($config, ['dispatchGetResponse']);
        $expectedResponse = new Response();
        $controller->expects($this->once())->method('dispatchGetResponse')->willReturn($expectedResponse);

        $response = $controller->delete(new Request([], [], ['entity' => 'id']));
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDeleteWithFormSubmittedAndValidReceivingFormEventResponse()
    {
        $config = [
            'delete' => [
                'form_valid_event_name' => 'test_event',
                'form' => $this->getMockBuilder(DeleteForm::class)->getMock(),
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->getControllerMock($config, ['dispatchGetResponse']);
        $expectedResponse = new Response();
        $controller->expects($this->once())->method('dispatchGetResponse')->willReturn($expectedResponse);

        $response = $controller->delete(new Request([], [], ['entity' => 'id']));
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDeleteWithFormSubmittedAndValidReceivingSuccessEventResponse()
    {
        $config = [
            'delete' => [
                'success_event_name' => 'test_event',
                'form' => $this->getMockBuilder(DeleteForm::class)->getMock(),
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());
        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->getControllerMock($config, ['dispatchGetResponse']);
        $expectedResponse = new Response();
        $controller->expects($this->once())->method('dispatchGetResponse')->willReturn($expectedResponse);
        $this->manager->expects($this->once())->method('deleteEntity');

        $response = $controller->delete(new Request([], [], ['entity' => 'id']));
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDeleteWithFormSubmittedAndValidWithRedirectRoute()
    {
        $config = [
            'delete' => [
                'success_redirect_to' => 'redirect_route',
                'form' => $this->getMockBuilder(DeleteForm::class)->getMock(),
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->getControllerMock($config, ['generateUrl']);
        $controller->expects($this->once())->method('generateUrl')->with($this->equalTo('redirect_route'))->willReturn('/redirect/to/route');
        $this->manager->expects($this->once())->method('deleteEntity');

        /** @var RedirectResponse $response */
        $response = $controller->delete(new Request([], [], ['entity' => 'id']));
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/redirect/to/route', $response->getTargetUrl());
    }

    public function testDeleteWithFormSubmittedAndValidWithDefaultRedirect()
    {
        $config = [
            'delete' => [
                'success_redirect_to' => '',
                'form' => $this->getMockBuilder(DeleteForm::class)->getMock(),
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->getControllerMock($config, []);
        $this->manager->expects($this->once())->method('deleteEntity');

        /** @var RedirectResponse $response */
        $response = $controller->delete(new Request([], [], ['entity' => 'id']));
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }
}
