<?php

namespace Softspring\Component\CrudlController\Helper;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Softspring\Component\CrudlController\Event\FilterEvent;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\Component\DoctrinePaginator\Collection\PaginatedCollection;
use Softspring\Component\DoctrinePaginator\Exception\InvalidFormTypeException;
use Softspring\Component\DoctrineQueryFilters\Exception\InvalidFilterValueException;
use Softspring\Component\DoctrineQueryFilters\Exception\MissingFromInQueryBuilderException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class ListActionHelper extends ActionHelper
{
    protected FormInterface $filterForm;
    protected FilterEvent $filterEvent;
    protected PaginatedCollection $results;

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

    public function renderResponse(string $view = null): Response
    {
        if ($this->request->isXmlHttpRequest() && $this->config['view_page']) {
            return parent::renderResponse($this->config['view_page']);
        } else {
            return parent::renderResponse($view);
        }
    }

    public function createFilterForm(): FormInterface
    {
        $type = $this->resolveFormClass();

        $this->filterForm = $this->formFactory->create($type, []);

        $this->filterForm->handleRequest($this->request);

        return $this->filterForm;
    }

    public function resolveFormClass(): ?string
    {
        if ($this->config['filter_form'] instanceof FormTypeInterface) {
            return get_class($this->config['filter_form']);
        }

        return $this->config['filter_form'];
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
