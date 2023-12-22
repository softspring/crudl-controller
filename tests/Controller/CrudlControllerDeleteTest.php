<?php

namespace Softspring\Component\CrudlController\Tests\Controller;

use Softspring\Component\CrudlController\Event\FormInvalidEvent;
use Softspring\Component\CrudlController\Event\FormValidEvent;
use Softspring\Component\CrudlController\Event\InitializeEvent;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Tests\Controller\Example\DeleteForm;
use Softspring\Component\Events\GetResponseRequestEvent;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CrudlControllerDeleteTest extends AbstractCrudlControllerTestCase
{
    public function testDeleteDenyUnlessGranted()
    {
        $configs = [
            'delete' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => DeleteForm::class,
                'is_granted' => 'ROLE_MISSING',
            ],
        ];

        $this->expectException(AccessDeniedException::class);

        $controller = $this->createController($configs);
        $controller->delete(new Request());
    }

    public function testDeleteWithNotFoundEventReturningResponse()
    {
        $configs = [
            'delete' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => DeleteForm::class,
                'is_granted' => null,
                'not_found_event_name' => 'not_found_event',
            ],
        ];

        $expectedResponse = new Response();

        $this->dispatcher->expects($this->once())->method('dispatch')->willReturnCallback(function ($event, string $eventName) use ($expectedResponse) {
            $event instanceof GetResponseRequestEvent  && $event->setResponse($expectedResponse);

            return $event;
        });

        $controller = $this->createController($configs);
        $response = $controller->delete(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDeleteWithNotFoundDefault()
    {
        $configs = [
            'delete' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => DeleteForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
            ],
        ];

        $this->expectException(NotFoundHttpException::class);
        $controller = $this->createController($configs);
        $controller->delete(new Request());
    }

    public function testDeleteWithInitializeEventReturningResponse()
    {
        $configs = [
            'delete' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => DeleteForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => 'initialize_event',
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'view_event_name' => null,
            ],
        ];

        $expectedResponse = new Response();
        $this->dispatcher->expects($this->any())->method('dispatch')->willReturnCallback(function ($event, string $eventName) use ($expectedResponse) {
            $event instanceof InitializeEvent && $event->setResponse($expectedResponse);

            return $event;
        });

        $controller = $this->createController($configs);
        $response = $controller->delete(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDeleteWithNoSubmittedFormAndViewEvent()
    {
        $config = [
            'delete' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => DeleteForm::class,
                'is_granted' => null,
                'not_found_event_name' => null,
                'initialize_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'view_event_name' => 'view_event',
            ],
        ];

        $this->formFactory->expects($this->once())->method('create')->willReturn($this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock());

        $this->repository->expects($this->once())->method('findOneBy')->willReturn(new \stdClass());

        $this->twig->expects($this->once())->method('render')->willReturn($config['delete']['view']);

        $controller = $this->createController($config);
        $response = $controller->delete(new Request());
        $this->assertEquals($config['delete']['view'], $response->getContent());
    }

    public function testDeleteWithFormSubmittedAndInvalidReceivingEventResponse()
    {
        $configs = [
            'delete' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => DeleteForm::class,
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
            $event instanceof FormInvalidEvent && $event->setResponse($expectedResponse);

            return $event;
        });

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(false);

        $controller = $this->createController($configs);
        $response = $controller->delete(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDeleteWithFormSubmittedAndValidReceivingFormEventResponse()
    {
        $configs = [
            'delete' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => DeleteForm::class,
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
            $event instanceof FormValidEvent && $event->setResponse($expectedResponse);

            return $event;
        });

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->createController($configs);
        $response = $controller->delete(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDeleteWithFormSubmittedAndValidReceivingSuccessEventResponse()
    {
        $configs = [
            'delete' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => DeleteForm::class,
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
            $event instanceof SuccessEvent && $event->setResponse($expectedResponse);

            return $event;
        });

        $form = $this->getMockBuilder(Form::class)->disableOriginalConstructor()->getMock();
        $this->formFactory->expects($this->once())->method('create')->willReturn($form);
        $form->expects($this->once())->method('handleRequest')->willReturn($form);
        $form->expects($this->once())->method('isSubmitted')->willReturn(true);
        $form->expects($this->once())->method('isValid')->willReturn(true);

        $controller = $this->createController($configs);
        $response = $controller->delete(new Request());
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDeleteWithFormSubmittedAndValidWithRedirectRoute()
    {
        $configs = [
            'delete' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => DeleteForm::class,
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
        $response = $controller->delete(new Request());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/redirect/to/route', $response->getTargetUrl());
    }

    public function testDeleteWithFormSubmittedAndValidWithDefaultRedirect()
    {
        $configs = [
            'delete' => [
                'entity_attribute' => 'entity',
                'param_converter_key' => 'id',
                'view' => 'template.html.twig',
                'form' => DeleteForm::class,
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
        $response = $controller->delete(new Request());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/', $response->getTargetUrl());
    }
}
