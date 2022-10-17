<?php

namespace Softspring\Component\CrudlController\Controller;

use Softspring\Component\CrudlController\Event\FilterEvent;
use Softspring\Component\CrudlController\Form\EntityListFilterFormInterface;
use Softspring\Component\CrudlController\Form\FormOptionsInterface;
use Softspring\Component\Events\GetResponseRequestEvent;
use Softspring\Component\Events\ViewEvent;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ListCrudlTrait
{
    /**
     * @param string|EntityListFilterFormInterface|null $listFilterForm
     */
    public function list(Request $request, $listFilterForm = null, array $config = []): Response
    {
        if (is_object($listFilterForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$listFilterForm method parameter is deprecated and will be removed in future versions. Please user the filter_form option in the config section.');
        }

        $config = array_replace_recursive($this->config['list'] ?? [], $config);
        $listFilterForm = $listFilterForm ?: $this->listFilterForm ?: $config['filter_form'] ?? null;

        if (is_string($listFilterForm)) {
            try {
                $listFilterForm = $this->container->get($listFilterForm);
            } catch (ServiceNotFoundException $e) {
                throw new \InvalidArgumentException('List filter is a string, if it\'s a service must be public');
            }
        }

        if (empty($config)) {
            throw new \InvalidArgumentException('List action configuration is empty');
        }

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseRequestEvent($request))) {
            return $response;
        }

        $repo = $this->manager->getRepository();

        if ($listFilterForm) {
            if (!$listFilterForm instanceof EntityListFilterFormInterface) {
                throw new \InvalidArgumentException(sprintf('List filter form must be an instance of %s', EntityListFilterFormInterface::class));
            }

            // additional fields for pagination and sorting
            $page = $listFilterForm->getPage($request);
            $rpp = $listFilterForm->getRpp($request);
            $orderSort = $listFilterForm->getOrder($request);

            $formClassName = get_class($listFilterForm);

            if ($listFilterForm instanceof FormOptionsInterface) {
                $formOptions = $listFilterForm->formOptions(null, $request);
            } elseif ($listFilterForm instanceof FormTypeInterface && method_exists($listFilterForm, 'formOptions')) {
                trigger_deprecation('softspring/crudl-controller', '5.x', 'If you want to use formOptions method you must implement %s interface.', FormOptionsInterface::class);
                $formOptions = $listFilterForm->formOptions(null, $request);
            } else {
                $formOptions = ['method' => 'POST'];
            }

            // filter form
            $form = $this->createForm($formClassName, [], $formOptions)->handleRequest($request);
            $filters = $form->isSubmitted() && $form->isValid() ? array_filter($form->getData()) : [];
        } else {
            $page = 1;
            $rpp = 10000;
            $orderSort = $config['default_order_sort'] ?? [];
            $form = null;
            $filters = [];
        }

        $this->dispatchFromConfig($config, 'filter_event_name', $filterEvent = new FilterEvent($filters, $orderSort, $page, $rpp));
        $filters = $filterEvent->getFilters();
        $orderSort = $filterEvent->getOrderSort();
        $page = $filterEvent->getPage();
        $rpp = $filterEvent->getRpp();

        // get results
        if ($repo instanceof PaginatedRepositoryInterface) {
            $entities = $repo->findPageBy($page, $rpp, $filters, $orderSort);
        } else {
            $entities = $repo->findBy($filters, $orderSort, $rpp, ($page - 1) * $rpp);
        }

        $entitiesAttribute = $config['entities_attribute'] ?? 'entities';

        $this->dispatchFromConfig($config, 'view_event_name', $event = new ViewEvent([
            'entities' => $entities, // @deprecated
            $entitiesAttribute => $entities,
            'filterForm' => $form instanceof FormInterface ? $form->createView() : null,
            'read_route' => $config['read_route'] ?? null,
        ], $request));

        if ($request->isXmlHttpRequest()) {
            return $this->render($config['view_page'], $event->getData()->getArrayCopy());
        } else {
            return $this->render($config['view'], $event->getData()->getArrayCopy());
        }
    }
}