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

class CrudlControllerUpdateTest extends AbstractCrudlControllerTestCase
{
    public function testUpdateDenyUnlessGranted()
    {
        $configs = [
            'update' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => UpdateForm::class,
                'is_granted' => 'ROLE_MISSING',
            ],
        ];

        $this->expectException(AccessDeniedException::class);

        $controller = $this->createController($configs);
        $controller->update(new Request());
    }

    public function testUpdateWithNotFoundEventReturningResponse()
    {
        $configs = [
            'update' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => UpdateForm::class,
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
        $response = $controller->update(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithNotFoundDefault()
    {
        $configs = [
            'update' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => UpdateForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
            ],
        ];

        $this->expectException(NotFoundHttpException::class);
        $controller = $this->createController($configs);
        $controller->update(new Request());
    }

    public function testUpdateWithInitializeEventReturningResponse()
    {
        $configs = [
            'update' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => UpdateForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
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
        $response = $controller->update(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithNoSubmittedFormAndViewEvent()
    {
        $config = [
            'update' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => UpdateForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'view_event_name' => 'view_event',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(new \stdClass());

        $this->twig->expects($this->once())->method('render')->willReturn($config['update']['view']);

        $controller = $this->createController($config);
        $response = $controller->update(new Request());
        $this->assertEquals($config['update']['view'], $response->getContent());
    }

    public function testUpdateWithFormSubmittedAndInvalidReceivingEventResponse()
    {
        $configs = [
            'update' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => UpdateForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_invalid_event_name' => 'form_invalid_event',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(new \stdClass());

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
        $response = $controller->update(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithFormSubmittedAndValidReceivingFormEventResponse()
    {
        $configs = [
            'update' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => UpdateForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_invalid_event_name' => null,
                'form_valid_event_name' => 'form_valid_event',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(new \stdClass());

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
        $response = $controller->update(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithFormSubmittedAndValidReceivingSuccessEventResponse()
    {
        $configs = [
            'update' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => UpdateForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_invalid_event_name' => null,
                'form_valid_event_name' => null,
                'success_event_name' => 'success_event',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(new \stdClass());

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
        $response = $controller->update(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testUpdateWithFormSubmittedAndValidWithRedirectRoute()
    {
        $configs = [
            'update' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => UpdateForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_invalid_event_name' => null,
                'form_valid_event_name' => null,
                'success_event_name' => null,
                'success_redirect_to' => 'redirect_route',
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(new \stdClass());

        $this->router->expects($this->once())->method('generate')->with($this->equalTo('redirect_route'))->willReturn('/redirect/to/route');

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->createController($configs);
        /** @var RedirectResponse $response */
        $response = $controller->update(new Request());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/redirect/to/route', $response->getTargetUrl());
    }

    public function testUpdateWithFormSubmittedAndValidWithDefaultRedirect()
    {
        $configs = [
            'update' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => UpdateForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_invalid_event_name' => null,
                'form_valid_event_name' => null,
                'success_event_name' => null,
                'success_redirect_to' => null,
            ],
        ];

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(new \stdClass());

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->createController($configs);

        /** @var RedirectResponse $response */
        $response = $controller->update(new Request());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }
}
