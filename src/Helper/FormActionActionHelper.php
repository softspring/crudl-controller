<?php

namespace Softspring\Component\CrudlController\Helper;

use Softspring\Component\CrudlController\Event\ApplyEvent;
use Softspring\Component\CrudlController\Event\FailureEvent;
use Softspring\Component\CrudlController\Event\FormInitEvent;
use Softspring\Component\CrudlController\Event\FormInvalidEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\FormValidEvent;
use Softspring\Component\CrudlController\Event\SuccessEvent;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\Component\Events\FormEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class FormActionActionHelper extends EntityActionHelper
{
    protected ?FormInterface $form = null;

    public function __construct(
        protected CrudlEntityManagerInterface $manager,
        protected EventDispatcherInterface $eventDispatcher,
        protected Environment $twig,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected RouterInterface $router,
        protected FormFactoryInterface $formFactory,
    ) {
        parent::__construct($manager, $eventDispatcher, $twig, $authorizationChecker, $router);
    }

    public function dispatchFormPrepare(array $options = ['method' => 'POST']): FormPrepareEvent
    {
        $event = new FormPrepareEvent($this->entity, $this->request, $options);

        if ($this->config['form_prepare_event_name']) {
            $this->_dispatch($event, $this->config['form_prepare_event_name']);
        }

        if (!$event->getType()) {
            $event->setType($this->resolveFormClass());
        }

        return $event;
    }

    public function resolveFormClass(): ?string
    {
        if (empty($this->config['form'])) {
            return null;
        }

        if ($this->config['form'] instanceof FormTypeInterface) {
            return get_class($this->config['form']);
        }

        return $this->config['form'];
    }

    public function createForm(FormPrepareEvent $formPrepareEvent): FormInterface
    {
        $type = $formPrepareEvent->getType();

        if (!$type) {
            throw new \RuntimeException('Form type not defined');
        }

        $data = $this->entity;
        $options = $formPrepareEvent->getFormOptions();

        $this->form = $this->formFactory->create($type, $data, $options);

        $this->form->handleRequest($this->request);

        return $this->form;
    }

    public function dispatchFormInit(): ?FormEvent
    {
        if (!$this->config['form_init_event_name']) {
            return null;
        }

        $this->_dispatch($event = new FormInitEvent($this->form, $this->request), $this->config['form_init_event_name']);

        return $event;
    }

    public function createViewData(array $data = []): \ArrayObject
    {
        $data['form'] = $this->form?->createView();
        $data[$this->config['entity_attribute']] = $this->entity;

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

    public function dispatchFormValid(): ?Response
    {
        if (!$this->config['form_valid_event_name']) {
            return null;
        }

        return $this->_dispatchGetResponse(new FormValidEvent($this->form, $this->request), $this->config['form_valid_event_name']);
    }

    public function dispatchApplyEvent(): ?bool
    {
        if (!$this->config['apply_event_name']) {
            return null;
        }

        $event = new ApplyEvent($this->entity, $this->request, $this->form);
        $this->_dispatch($event, $this->config['apply_event_name']);

        if ($event->getEntity()) {
            $this->entity = $event->getEntity();
        }

        return $event->isApplied();
    }

    public function dispatchSuccess(): ?Response
    {
        if (!$this->config['success_event_name']) {
            return null;
        }

        return $this->_dispatchGetResponse(new SuccessEvent($this->entity, $this->request), $this->config['success_event_name']);
    }

    public function dispatchFailure(\Exception $e): ?Response
    {
        if (!$this->config['failure_event_name']) {
            return null;
        }

        return $this->_dispatchGetResponse(new FailureEvent($this->entity, $this->request, $e, $this->form), $this->config['failure_event_name']);
    }

    public function dispatchFormInvalid(): ?Response
    {
        if (!$this->config['form_invalid_event_name']) {
            return null;
        }

        return $this->_dispatchGetResponse(new FormInvalidEvent($this->form, $this->request), $this->config['form_invalid_event_name']);
    }
}
