<?php

namespace Softspring\Component\CrudlController\Tests\Controller;

use Softspring\Component\CrudlController\Controller\CrudlController;
use Softspring\Component\CrudlController\Tests\Controller\Example\CreateForm;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CrudlControllerCreateTest extends AbstractCrudlControllerTestCase
{
    public function testCreateEmptyConfiguration()
    {
        $controller = new CrudlController($this->manager, $this->dispatcher);

        $this->expectException(\InvalidArgumentException::class);

        $controller->create(new Request());
    }

    public function testCreateDenyUnlessGranted()
    {
        $config = [
            'create' => [
                'is_granted' => 'ROLE_MISSING',
            ],
        ];

        $this->expectException(AccessDeniedException::class);

        $controller = $this->getControllerMock($config, ['denyAccessUnlessGranted']);
        $controller->expects($this->once())->method('denyAccessUnlessGranted')->willThrowException(new AccessDeniedException());
        $controller->create(new Request());
    }

    public function testCreateWithNoForm()
    {
        $config = [
            'create' => [
                'initialize_event_name' => '',
            ],
        ];

        $controller = new CrudlController($this->manager, $this->dispatcher, null, null, null, null, $config);

        $this->expectException(\InvalidArgumentException::class);

        $controller->create(new Request());
    }

    public function testCreateWithInitializeEventReturningResponse()
    {
        $config = [
            'create' => [
                'initialize_event_name' => 'test_event',
                'view' => 'test_view.html.twig',
            ],
        ];

        $createForm = $this->getMockBuilder(CreateForm::class)->getMock();

        $expectedResponse = new Response();

        $controller = $this->getControllerMock($config, ['dispatchGetResponse'], null, $createForm);
        $controller->expects($this->once())->method('dispatchGetResponse')->willReturn($expectedResponse);

        $response = $controller->create(new Request());

        $this->assertEquals($expectedResponse, $response);
    }

    public function testCreateWithNoSubmittedFormAndViewEvent()
    {
        $config = [
            'create' => [
                'view' => 'test_view.html.twig',
                'view_event_name' => 'test_event',
            ],
        ];

        // assertion only one dispatch call
        $this->dispatcher->expects($this->once())->method('dispatch');

        $createForm = $this->getMockBuilder(CreateForm::class)->getMock();

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);

        $controller = $this->getControllerMock($config, ['renderView'], null, $createForm);
        $controller->expects($this->once())->method('renderView')->willReturn($config['create']['view']);

        $response = $controller->create(new Request());

        $this->assertEquals($config['create']['view'], $response->getContent());
    }

    public function testCreateWithFormSubmittedAndInvalidReceivingEventResponse()
    {
        $config = [
            'create' => [
                'form_invalid_event_name' => 'test_event',
            ],
        ];

        $createForm = $this->getMockBuilder(CreateForm::class)->getMock();

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(false);

        $controller = $this->getControllerMock($config, ['dispatchGetResponse'], null, $createForm);
        $expectedResponse = new Response();
        $controller->expects($this->once())->method('dispatchGetResponse')->willReturn($expectedResponse);

        $response = $controller->create(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testCreateWithFormSubmittedAndValidReceivingFormEventResponse()
    {
        $config = [
            'create' => [
                'form_valid_event_name' => 'test_event',
            ],
        ];

        $createForm = $this->getMockBuilder(CreateForm::class)->getMock();

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->getControllerMock($config, ['dispatchGetResponse'], null, $createForm);
        $expectedResponse = new Response();
        $controller->expects($this->once())->method('dispatchGetResponse')->willReturn($expectedResponse);

        $response = $controller->create(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testCreateWithFormSubmittedAndValidReceivingSuccessEventResponse()
    {
        $config = [
            'create' => [
                'success_event_name' => 'test_event',
            ],
        ];

        $createForm = $this->getMockBuilder(CreateForm::class)->getMock();

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->getControllerMock($config, ['dispatchGetResponse'], null, $createForm);
        $expectedResponse = new Response();
        $controller->expects($this->once())->method('dispatchGetResponse')->willReturn($expectedResponse);

        $response = $controller->create(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testCreateWithFormSubmittedAndValidWithRedirectRoute()
    {
        $config = [
            'create' => [
                'success_redirect_to' => 'redirect_route',
            ],
        ];

        $createForm = $this->getMockBuilder(CreateForm::class)->getMock();

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->getControllerMock($config, ['generateUrl'], null, $createForm);
        $controller->expects($this->once())->method('generateUrl')->with($this->equalTo('redirect_route'))->willReturn('/redirect/to/route');

        /** @var RedirectResponse $response */
        $response = $controller->create(new Request());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/redirect/to/route', $response->getTargetUrl());
    }

    public function testCreateWithFormSubmittedAndValidWithDefaultRedirect()
    {
        $config = [
            'create' => [
                'success_redirect_to' => '',
            ],
        ];

        $createForm = $this->getMockBuilder(CreateForm::class)->getMock();

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->getControllerMock($config, [], null, $createForm);

        /** @var RedirectResponse $response */
        $response = $controller->create(new Request());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }
}
