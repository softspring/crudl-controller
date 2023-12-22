<?php

namespace Softspring\Component\CrudlController\Helper;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Softspring\Component\CrudlController\Event\FilterEvent;
use Softspring\Component\CrudlController\Event\FormInitEvent;
use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\ViewEvent;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\Component\DoctrinePaginator\Collection\PaginatedCollection;
use Softspring\Component\DoctrinePaginator\Exception\InvalidFormTypeException;
use Softspring\Component\DoctrineQueryFilters\Exception\InvalidFilterValueException;
use Softspring\Component\DoctrineQueryFilters\Exception\MissingFromInQueryBuilderException;
use Softspring\Component\Events\FormEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class ListActionHelper extends ActionHelper
{
    protected ?FormInterface $filterForm = null;
    protected FilterEvent $filterEvent;
    protected PaginatedCollection $results;

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

    public function renderResponse(ViewEvent $event): Response
    {
        if ($this->request->isXmlHttpRequest() && !$event->getTemplate() && $this->config['view_page']) {
            $event->setTemplate($this->config['view_page']);
        }

        return parent::renderResponse($event);
    }

    public function dispatchFormPrepare(array $options = ['method' => 'GET']): FormPrepareEvent
    {
        $event = new FormPrepareEvent(null, $this->request, $options);

        if ($this->config['filter_form_prepare_event_name']) {
            $this->_dispatch($event, $this->config['filter_form_prepare_event_name']);
        }

        if (!$event->getType()) {
            $event->setType($this->resolveFormClass());
        }

        return $event;
    }

    public function resolveFormClass(): string
    {
        if ($this->config['filter_form'] instanceof FormTypeInterface) {
            return get_class($this->config['filter_form']);
        }

        return $this->config['filter_form'];
    }

    public function createFilterForm(FormPrepareEvent $formPrepareEvent): ?FormInterface
    {
        $type = $formPrepareEvent->getType();

        $this->filterForm = $this->formFactory->create($type, [], $formPrepareEvent->getFormOptions());

        $this->filterForm->handleRequest($this->request);

        return $this->filterForm;
    }

    public function dispatchFormInit(): ?FormEvent
    {
        if (!$this->config['filter_form_init_event_name']) {
            return null;
        }

        $this->_dispatch($event = new FormInitEvent($this->filterForm, $this->request), $this->config['filter_form_init_event_name']);

        return $event;
    }

    /**
     * @throws InvalidFormTypeException
     */
    public function dispatchResultFilterEvent(): FilterEvent
    {
        $this->filterEvent = FilterEvent::createFromFilterForm($this->filterForm, $this->request);

        if ($this->config['filter_event_name']) {
            $this->_dispatch($this->filterEvent, $this->config['filter_event_name']);
        }

        return $this->filterEvent;
    }

    /**
     * @throws InvalidFilterValueException
     * @throws MissingFromInQueryBuilderException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function queryResults(): PaginatedCollection
    {
        $this->results = $this->filterEvent->queryPage();

        return $this->results;
    }

    public function createViewData(array $data = []): \ArrayObject
    {
        $data[$this->config['entities_attribute']] = $this->results;
        $data['filterForm'] = $this->filterForm->createView();
        $data['read_route'] = $this->config['read_route'];

        return parent::createViewData($data);
    }
}
