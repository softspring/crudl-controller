<?php

namespace Softspring\Component\CrudlController\Helper;

use Exception;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\WorkflowInterface;
use Twig\Environment;

class TransitionActionHelper extends FormActionActionHelper
{
    public function __construct(
        CrudlEntityManagerInterface $manager,
        EventDispatcherInterface $eventDispatcher,
        Environment $twig,
        AuthorizationCheckerInterface $authorizationChecker,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        protected Registry $registry,
    ) {
        parent::__construct($manager, $eventDispatcher, $twig, $authorizationChecker, $router, $formFactory);
    }

    protected ?string $transitionName = null;

    /**
     * First of all, get the transition name from the request.
     * It's mandatory to have a transition name in the request to use this helper.
     *
     * @throws Exception
     */
    public function initialize(): void
    {
        $this->transitionName = $this->request->attributes->get($this->config['transition_attribute'] ?? 'transition');
        $this->request->attributes->set('_crudl_action_transition_name', $this->transitionName);

        if (empty($this->transitionName)) {
            throw new Exception('Transition name not found in request');
        }
    }

    protected ?WorkflowInterface $workflow = null;
    protected ?Transition $transition = null;
    protected ?array $transitionMetadata = null;

    /**
     * This method is called after initialize, after grant check, after entity is found.
     * So it initializes the transition object and metadata, and set it to request to allow use it in listeners.
     *
     * @throws Exception
     */
    public function initTransition(): void
    {
        // get workflow object, first try to get it from the configuration, then the default one
        $this->workflow = $this->registry->get($this->getEntity(), $this->config['workflow_name'] ?? null);

        // get transition object
        $this->transition = $this->workflow->getEnabledTransition($this->getEntity(), $this->transitionName);
        $this->request->attributes->set('_crudl_action_transition', $this->transition);

        // no transition found
        if (!$this->transition) {
            // TODO set custom exception class
            throw new Exception('Transition not found');
        }

        // get transition metadata
        $this->transitionMetadata = $this->workflow->getMetadataStore()->getTransitionMetadata($this->transition);
        $this->request->attributes->set('_crudl_action_transition_metadata', $this->transitionMetadata);
    }

    /**
     * This method checks if the transition is applicable to the entity and the user is allowed to apply it (it check guards).
     *
     * @throws AccessDeniedException
     */
    public function checkCanTransition(): void
    {
        if (!$this->workflow->can($this->getEntity(), $this->transitionName)) {
            $exception = new AccessDeniedException(sprintf('Transition %s is not allowed.', $this->transitionName));
            $exception->setAttributes([$this->transitionName]);
            $exception->setSubject($this->getEntity());

            throw $exception;
        }
    }

    /**
     * In transition actions is not mandatory to have a form, but if it is defined in transition metadata, use it
     * Otherwise, use the parent method to resolve the form class.
     */
    public function resolveFormClass(): string|array|null
    {
        if ($this->transitionMetadata['crudl_form'] ?? null) {
            return $this->transitionMetadata['crudl_form'];
        }

        return parent::resolveFormClass();
    }

    /**
     * In transition actions is not mandatory to have a form, so do not fail if form type is not defined.
     */
    public function createForm(FormPrepareEvent $formPrepareEvent): FormInterface|false
    {
        if (!$formPrepareEvent->getType()) {
            return false;
        }

        return parent::createForm($formPrepareEvent);
    }

    /**
     * Core method to apply the transition.
     */
    public function applyTransition(): void
    {
        $entity = $this->getEntity();

        $transitionContext = []; // this can be modified by event listeners

        $this->workflow->apply($entity, $this->transitionName, $transitionContext);
        $this->manager->saveEntity($entity);
    }

    /**
     * This method overrides the parent one to allow read view template from transition metadata.
     */
    public function dispatchViewEvent(): ViewEvent
    {
        $viewTemplate = $this->transitionMetadata['crudl_template'] ?? $this->config['view'];

        $event = new ViewEvent($this->viewData, $viewTemplate, $this->request);
        $event->setManager($this->manager);
        $event->setConfig($this->config);

        if ($this->config['view_event_name']) {
            $this->_dispatch($event, $this->config['view_event_name']);
        }

        return $event;
    }
}
