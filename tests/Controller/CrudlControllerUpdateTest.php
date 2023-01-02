<?php

namespace Softspring\Component\CrudlController\Tests\Controller;

use Softspring\Component\CrudlController\Controller\CrudlController;
use Softspring\Component\CrudlController\Exception\EmptyConfigException;
use Softspring\Component\CrudlController\Exception\InvalidFormException;
use Softspring\Component\CrudlController\Tests\Controller\Example\UpdateForm;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CrudlControllerUpdateTest extends AbstractCrudlControllerTestCase
{
    public function testUpdateEmptyConfiguration()
    {
        $controller = new CrudlController($this->manager, $this->dispatcher);

        $this->expectException(EmptyConfigException::class);

        $controller->update(new Request([], [], ['entity' => 'id']));
    }

    public function testUpdateDenyUnlessGranted()
    {
        $config = [
            'update' => [
                'is_granted' => 'ROLE_MISSING',
            ],
        ];

        $this->expectException(AccessDeniedException::class);

        $controller = $this->getControllerMock($config, ['denyAccessUnlessGranted']);
        $controller->expects($this->once())->method('denyAccessUnlessGranted')->willThrowException(new AccessDeniedException());
        $controller->update(new Request([], [], ['entity' => 'id']));
    }

    public function testUpdateWithNotFoundEntity()
    {
        $config = [
            'update' => [
                'initialize_event_name' => '',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(null);

        $controller = new CrudlController($this->manager, $this->dispatcher, $config);

        $this->expectException(NotFoundHttpException::class);

        $controller->update(new Request([], [], ['entity' => 'id']));
    }

    public function testUpdateWithNoForm()
    {
        $config = [
            'update' => [
                'initialize_event_name' => '',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(new \stdClass());

        $controller = new CrudlController($this->manager, $this->dispatcher, $config);

        $this->expectException(InvalidFormException::class);

        $controller->update(new Request([], [], ['entity' => 'id']));
    }

    public function testUpdateWithInitializeEventReturningResponse()
    {
        $config = [
            'update' => [
                'initialize_event_name' => 'test_event',
                'view' => 'test_view.html.twig',
                'form' => $this->getMockBuilder(UpdateForm::class)->getMock(),
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        $expectedResponse = new Response();

        $controller = $this->getControllerMock($config, ['dispatchGetResponse']);
        $controller->expects($this->once())->method('dispatchGetResponse')->willReturn($expectedResponse);

        $response = $controller->update(new Request([], [], ['entity' => 'id']));

        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithNoSubmittedFormAndViewEvent()
    {
        $config = [
            'update' => [
                'view' => 'test_view.html.twig',
                'view_event_name' => 'test_event',
                'form' => $this->getMockBuilder(UpdateForm::class)->getMock(),
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        // assertion only one dispatch call
        $this->dispatcher->expects($this->once())->method('dispatch');

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);

        $controller = $this->getControllerMock($config, ['renderView']);
        $controller->expects($this->once())->method('renderView')->willReturn($config['update']['view']);

        $response = $controller->update(new Request([], [], ['entity' => 'id']));

        $this->assertEquals($config['update']['view'], $response->getContent());
    }

    public function testUpdateWithFormSubmittedAndInvalidReceivingEventResponse()
    {
        $config = [
            'update' => [
                'form_invalid_event_name' => 'test_event',
                'form' => $this->getMockBuilder(UpdateForm::class)->getMock(),
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

        $response = $controller->update(new Request([], [], ['entity' => 'id']));
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithFormSubmittedAndValidReceivingFormEventResponse()
    {
        $config = [
            'update' => [
                'form_valid_event_name' => 'test_event',
                'form' => $this->getMockBuilder(UpdateForm::class)->getMock(),
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

        $response = $controller->update(new Request([], [], ['entity' => 'id']));
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithFormSubmittedAndValidReceivingSuccessEventResponse()
    {
        $config = [
            'update' => [
                'success_event_name' => 'test_event',
                'form' => $this->getMockBuilder(UpdateForm::class)->getMock(),
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

        $response = $controller->update(new Request([], [], ['entity' => 'id']));
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithFormSubmittedAndValidWithRedirectRoute()
    {
        $config = [
            'update' => [
                'success_redirect_to' => 'redirect_route',
                'form' => $this->getMockBuilder(UpdateForm::class)->getMock(),
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

        /** @var RedirectResponse $response */
        $response = $controller->update(new Request([], [], ['entity' => 'id']));
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/redirect/to/route', $response->getTargetUrl());
    }

    public function testUpdateWithFormSubmittedAndValidWithDefaultRedirect()
    {
        $config = [
            'update' => [
                'success_redirect_to' => '',
                'form' => $this->getMockBuilder(UpdateForm::class)->getMock(),
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn($entity = new \stdClass());

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->getControllerMock($config, []);

        /** @var RedirectResponse $response */
        $response = $controller->update(new Request([], [], ['entity' => 'id']));
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }
}
