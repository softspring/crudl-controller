<?php

namespace Softspring\Component\CrudlController\Helper;

use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\Component\CrudlController\Event\GetResponseEntityExceptionEvent;
use Softspring\Component\CrudlController\Event\GetResponseFormEvent;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\Component\Events\FormEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class FormActionActionHelper extends EntityActionHelper
{
    protected FormInterface $form;

    public function __construct(
        protected CrudlEntityManagerInterface $manager,
        protected EventDispatcherInterface $eventDispatcher,
        protected Environment $twig,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected RouterInterface $router,
        protected FormFactory $formFactory,
    ) {
        parent::__construct($manager, $eventDispatcher, $twig, $authorizationChecker, $router);
    }

    public function createForm(?FormPrepareEvent $formPrepareEvent): FormInterface
    {
        $type = $this->resolveFormClass();
        $data = $this->entity;
        $options = $formPrepareEvent?->getFormOptions() ?? [];

        $this->form = $this->formFactory->create($type, $data, $options);

        $this->form->handleRequest($this->request);

        return $this->form;
    }

    public function createViewData(array $data = []): \ArrayObject
    {
        $data['form'] = $this->form->createView();

        return parent::createViewData($data);
    }

    public function successRedirect(): RedirectResponse
    {
        $url = '/';

        if ($this->config['success_redirect_to']) {
            $url = $this->router->generate($this->config['success_redirect_to'], [$this->config['entity_attribute'] => $this->getEntity()]);
        }

        return new RedirectResponse($url, Response::HTTP_FOUND);
    }

    public function resolveFormClass(): string
    {
        if ($this->config['form'] instanceof FormTypeInterface) {
            return get_class($this->config['form']);
        }

        return $this->config['form'];
    }

    public function dispatchFormPrepare(array $options = ['method' => 'POST']): ?FormPrepareEvent
    {
        if (!$this->config['form_prepare_event_name']) {
            return null;
        }

        $this->_dispatch($event = new FormPrepareEvent($this->entity, $this->request, $options), $this->config['form_prepare_event_name']);

        return $event;
    }

    public function dispatchFormInit(): ?FormEvent
    {
        if (!$this->config['form_init_event_name']) {
            return null;
        }

        $this->_dispatch($event = new FormEvent($this->form, $this->request), $this->config['form_init_event_name']);

        return $event;
    }

    public function dispatchFormValid(): ?Response
    {
        if (!$this->config['form_valid_event_name']) {
            return null;
        }

        return $this->_dispatchGetResponse(new GetResponseFormEvent($this->form, $this->request), $this->config['form_valid_event_name']);
    }

    public function dispatchSuccess(): ?Response
    {
        if (!$this->config['success_event_name']) {
            return null;
        }

        return $this->_dispatchGetResponse(new GetResponseEntityEvent($this->entity, $this->request), $this->config['success_event_name']);
    }

    public function dispatchException(\Exception $e): ?Response
    {
        if (!$this->config['exception_event_name']) {
            return null;
        }

        return $this->_dispatchGetResponse(new GetResponseEntityExceptionEvent($this->entity, $this->request, $e), $this->config['exception_event_name']);
    }

    public function dispatchFormInvalid(): ?Response
    {
        if (!$this->config['form_invalid_event_name']) {
            return null;
        }

        return $this->_dispatchGetResponse(new GetResponseFormEvent($this->form, $this->request), $this->config['form_invalid_event_name']);
    }
}
