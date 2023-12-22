<?php

namespace Softspring\Component\CrudlController\Tests\Controller;

use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\Component\CrudlController\Event\GetResponseFormEvent;
use Softspring\Component\CrudlController\Tests\Controller\Example\CreateForm;
use Softspring\Component\Events\GetResponseRequestEvent;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CrudlControllerCreateTest extends AbstractCrudlControllerTestCase
{
    public function testCreateDenyUnlessGranted()
    {
        $configs = [
            'create' => [
                'entity_attribute' => 'entity',
                'view' => 'template.html.twig',
                'form' => CreateForm::class,
                'is_granted' => 'ROLE_MISSING',
            ],
        ];

        $this->expectException(AccessDeniedException::class);

        $controller = $this->createController($configs);
        $controller->create(new Request());
    }

    public function testCreateWithInitializeEventReturningResponse()
    {
        $configs = [
            'create' => [
                'entity_attribute' => 'entity',
                'view' => 'template.html.twig',
                'form' => CreateForm::class,
                'is_granted' => null,
                'initialize_event_name' => 'initialize_event',
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'view_event_name' => null,
            ],
        ];

        $expectedResponse = new Response();

        $this->dispatcher->expects($this->once())->method('dispatch')->willReturnCallback(function ($event, string $eventName) use ($expectedResponse) {
            $eventName == 'initialize_event' && $event instanceof GetResponseRequestEvent && $event->setResponse($expectedResponse);

            return $event;
        });

        $controller = $this->createController($configs);
        $response = $controller->create(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testCreateWithNoSubmittedFormAndViewEvent()
    {
        $config = [
            'create' => [
                'entity_attribute' => 'entity',
                'view' => 'template.html.twig',
                'form' => CreateForm::class,
                'is_granted' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'view_event_name' => 'view_event',
            ],
        ];

        $this->formFactory->expects($this->once())->method('create')->willReturn($this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock());

        $this->twig->expects($this->once())->method('render')->willReturn($config['create']['view']);

        $controller = $this->createController($config);
        $response = $controller->create(new Request());
        $this->assertEquals($config['create']['view'], $response->getContent());
    }

    public function testCreateWithFormSubmittedAndInvalidReceivingEventResponse()
    {
        $configs = [
            'create' => [
                'entity_attribute' => 'entity',
                'view' => 'template.html.twig',
                'form' => CreateForm::class,
                'is_granted' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_invalid_event_name' => 'form_invalid_event',
            ],
        ];

        $expectedResponse = new RedirectResponse('/');
        $this->dispatcher->expects($this->once())->method('dispatch')->willReturnCallback(function ($event, string $eventName) use ($expectedResponse) {
            $eventName == 'form_invalid_event' && $event instanceof GetResponseFormEvent && $event->setResponse($expectedResponse);

            return $event;
        });

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(false);

        $controller = $this->createController($configs);
        $response = $controller->create(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testCreateWithFormSubmittedAndValidReceivingFormEventResponse()
    {
        $configs = [
            'create' => [
                'entity_attribute' => 'entity',
                'view' => 'template.html.twig',
                'form' => CreateForm::class,
                'is_granted' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_invalid_event_name' => null,
                'form_valid_event_name' => 'form_valid_event',
            ],
        ];

        $expectedResponse = new RedirectResponse('/');
        $this->dispatcher->expects($this->once())->method('dispatch')->willReturnCallback(function ($event, string $eventName) use ($expectedResponse) {
            $eventName == 'form_valid_event' && $event instanceof GetResponseFormEvent && $event->setResponse($expectedResponse);

            return $event;
        });

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->createController($configs);
        $response = $controller->create(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testCreateWithFormSubmittedAndValidReceivingSuccessEventResponse()
    {
        $configs = [
            'create' => [
                'entity_attribute' => 'entity',
                'view' => 'template.html.twig',
                'form' => CreateForm::class,
                'is_granted' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_invalid_event_name' => null,
                'form_valid_event_name' => null,
                'success_event_name' => 'success_event',
            ],
        ];

        $expectedResponse = new RedirectResponse('/');
        $this->dispatcher->expects($this->once())->method('dispatch')->willReturnCallback(function ($event, string $eventName) use ($expectedResponse) {
            $eventName == 'success_event' && $event instanceof GetResponseEntityEvent && $event->setResponse($expectedResponse);

            return $event;
        });

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->createController($configs);
        $response = $controller->create(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testCreateWithFormSubmittedAndValidWithRedirectRoute()
    {
        $configs = [
            'create' => [
                'entity_attribute' => 'entity',
                'view' => 'template.html.twig',
                'form' => CreateForm::class,
                'is_granted' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_invalid_event_name' => null,
                'form_valid_event_name' => null,
                'success_event_name' => null,
                'success_redirect_to' => 'redirect_route',
            ],
        ];

        $this->router->expects($this->once())->method('generate')->with($this->equalTo('redirect_route'))->willReturn('/redirect/to/route');

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->createController($configs);
        /** @var RedirectResponse $response */
        $response = $controller->create(new Request());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/redirect/to/route', $response->getTargetUrl());
    }

    public function testCreateWithFormSubmittedAndValidWithDefaultRedirect()
    {
        $configs = [
            'create' => [
                'entity_attribute' => 'entity',
                'view' => 'template.html.twig',
                'form' => CreateForm::class,
                'is_granted' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_invalid_event_name' => null,
                'form_valid_event_name' => null,
                'success_event_name' => null,
                'success_redirect_to' => null,
            ],
        ];

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->createController($configs);

        /** @var RedirectResponse $response */
        $response = $controller->create(new Request());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }
}
