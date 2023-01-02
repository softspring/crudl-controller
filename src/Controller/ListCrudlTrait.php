<?php

namespace Softspring\Component\CrudlController\Controller;

use Softspring\Component\CrudlController\Event\FilterEvent;
use Softspring\Component\DoctrinePaginator\Paginator;
use Softspring\Component\DoctrineQueryFilters\FilterFormInterface;
use Softspring\Component\DoctrineQueryFilters\Filters;
use Softspring\Component\Events\GetResponseRequestEvent;
use Softspring\Component\Events\ViewEvent;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ListCrudlTrait
{
    public function list(Request $request, array $config = []): Response
    {
        $config = array_replace_recursive($this->config['list'] ?? [], $config);

        if (empty($config)) {
            throw new \InvalidArgumentException('List action configuration is empty');
        }

        $listFilterForm = $config['filter_form'] ?? null;

        if (is_string($listFilterForm)) {
            try {
                $listFilterForm = $this->container->get($listFilterForm);
            } catch (ServiceNotFoundException $e) {
                throw new \InvalidArgumentException('List filter is a string, if it\'s a service must be public');
            }
        }

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseRequestEvent($request))) {
            return $response;
        }

        $repo = $this->manager->getRepository();

        $form = null;
        if ($listFilterForm) {
            if (!$listFilterForm instanceof FilterFormInterface) {
                throw new \InvalidArgumentException(sprintf('List filter form must be an instance of %s', FilterFormInterface::class));
            }

            $formClassName = get_class($listFilterForm);

            // filter form
            $form = $this->createForm($formClassName)->handleRequest($request);
            $filters = $form->isSubmitted() && $form->isValid() ? array_filter($form->getData()) : [];

            $formCompiledOptions = $form->getConfig()->getOptions();
            $page = $request->get($formCompiledOptions['page_field_name'], 1);
            $rpp = $form->get($formCompiledOptions['rpp_field_name'])->getData() ?? $formCompiledOptions['rpp_default_value'];
            $orderSort = [$form->get($formCompiledOptions['order_field_name'])->getData() ?? $formCompiledOptions['order_default_value'] => $form->get($formCompiledOptions['order_direction_field_name'])->getData() ?? $formCompiledOptions['order_direction_default_value']];
            $filterMode = $formCompiledOptions['query_builder_mode'];

            $filterEvent = new FilterEvent($filters, $orderSort, $page, $rpp);
        } else {
            // without filter form, query all entities without filtering and pagination
            $filterEvent = new FilterEvent([], $config['default_order_sort'] ?? []);
            $filterMode = Filters::MODE_AND;
        }

        $this->dispatchFromConfig($config, 'filter_event_name', $filterEvent);

        $entities = Paginator::queryPage($repo->createQueryBuilder('a'), $filterEvent->getPage(), $filterEvent->getRpp(), $filterEvent->getFilters(), $filterEvent->getOrderSort(), $filterMode);

        $this->dispatchFromConfig($config, 'view_event_name', $event = new ViewEvent([
            $config['entities_attribute'] ?? 'entities' => $entities,
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
