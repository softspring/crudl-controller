<?php

namespace Softspring\Component\CrudlController\Controller;

use Jhg\DoctrinePagination\ORM\PaginatedRepositoryInterface;
use Softspring\Component\CrudlController\Event\FilterEvent;
use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\Component\CrudlController\Event\GetResponseEntityExceptionEvent;
use Softspring\Component\CrudlController\Event\GetResponseFormEvent;
use Softspring\Component\CrudlController\Form\DefaultDeleteForm;
use Softspring\Component\CrudlController\Form\EntityListFilterFormInterface;
use Softspring\Component\CrudlController\Form\FormOptionsInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\CoreBundle\Controller\Traits\DispatchGetResponseTrait;
use Softspring\CoreBundle\Event\FormEvent;
use Softspring\CoreBundle\Event\GetResponseEventInterface;
use Softspring\CoreBundle\Event\GetResponseRequestEvent;
use Softspring\CoreBundle\Event\ViewEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Entity CRUDL controller (CRUD+listing).
 */
class CrudlController extends AbstractController
{
    use DispatchGetResponseTrait;

    protected CrudlEntityManagerInterface $manager;

    /**
     * @deprecated
     */
    protected ?EntityListFilterFormInterface $listFilterForm;

    /**
     * @var FormTypeInterface|string|null
     *
     * @deprecated
     */
    protected $createForm;

    /**
     * @var FormTypeInterface|string|null
     *
     * @deprecated
     */
    protected $updateForm;

    /**
     * @var FormTypeInterface|string|null
     *
     * @deprecated
     */
    protected $deleteForm;

    protected array $config;

    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @param FormTypeInterface|string|null $createForm
     * @param FormTypeInterface|string|null $updateForm
     * @param FormTypeInterface|string|null $deleteForm
     */
    public function __construct(CrudlEntityManagerInterface $manager, EventDispatcherInterface $eventDispatcher, ?EntityListFilterFormInterface $listFilterForm = null, $createForm = null, $updateForm = null, $deleteForm = null, array $config = [])
    {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
        $this->listFilterForm = $listFilterForm;
        if (is_object($listFilterForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$listFilterForm constructor parameter is deprecated and will be removed in future versions. Please user the filter_form option in the config section.');
        }
        $this->createForm = $createForm;
        if (is_object($createForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$createForm constructor parameter is deprecated and will be removed in future versions. Please user the form option in the config section.');
        }
        $this->updateForm = $updateForm;
        if (is_object($updateForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$updateForm constructor parameter is deprecated and will be removed in future versions. Please user the form option in the config section.');
        }
        $this->deleteForm = $deleteForm;
        if (is_object($deleteForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$deleteForm constructor parameter is deprecated and will be removed in future versions. Please user the form option in the config section.');
        }
        $this->config = $config;
        $this->config['entity_attribute'] = $this->config['entity_attribute'] ?? 'entity';
    }

    /**
     * @param string|FormTypeInterface|null $createForm
     */
    public function create(Request $request, $createForm = null, array $config = []): Response
    {
        if (is_object($createForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$createForm method parameter is deprecated and will be removed in future versions. Please user the form option in the config section.');
        }

        $config = array_replace_recursive($this->config['create'] ?? [], $config);
        $createForm = $createForm ?: $this->createForm ?: $config['form'] ?? null;

        if (empty($config)) {
            throw new \InvalidArgumentException('Create action configuration is empty');
        }

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if (!$createForm instanceof FormTypeInterface && !is_string($createForm)) {
            throw new \InvalidArgumentException(sprintf('Create form must be an instance of %s or a class name', FormTypeInterface::class));
        }

        $entity = $this->manager->createEntity();

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
            return $response;
        }

        if ($createForm instanceof FormOptionsInterface) {
            $formOptions = $createForm->formOptions($entity, $request);
        } elseif ($createForm instanceof FormTypeInterface && method_exists($createForm, 'formOptions')) {
            trigger_deprecation('softspring/crudl-controller', '5.x', 'If you want to use formOptions method you must implement %s interface.', FormOptionsInterface::class);
            $formOptions = $createForm->formOptions($entity, $request);
        } else {
            $formOptions = ['method' => 'POST'];
        }

        $formClassName = $createForm instanceof FormTypeInterface ? get_class($createForm) : $createForm;

        $form = $this->createForm($formClassName, $entity, $formOptions)->handleRequest($request);

        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }

                $this->manager->saveEntity($entity);

                if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
                    return $response;
                }

                return $this->redirect(!empty($config['success_redirect_to']) ? $this->generateUrl($config['success_redirect_to']) : '/');
            } else {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            $this->config['entity_attribute'] => $entity,
            'form' => $form->createView(),
        ]);

        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData, $request));

        return $this->render($config['view'], $viewData->getArrayCopy());
    }

    public function read(Request $request, array $config = []): Response
    {
        $config = array_replace_recursive($this->config['read'] ?? [], $config);

        $entity = $request->attributes->get($this->config['entity_attribute']);

        if (empty($config)) {
            throw new \InvalidArgumentException('Read action configuration is empty');
        }

        // convert entity
        $entity = $this->manager->getRepository()->findOneBy([$config['param_converter_key'] ?? 'id' => $entity]);

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if (!$entity) {
            throw $this->createNotFoundException('Entity not found');
        }

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
            return $response;
        }

        // show view
        $viewData = new \ArrayObject([
            $this->config['entity_attribute'] => $entity,
        ]);

        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData, $request));

        return $this->render($config['view'], $viewData->getArrayCopy());
    }

    /**
     * @param string|FormTypeInterface|null $updateForm
     */
    public function update(Request $request, $updateForm = null, array $config = []): Response
    {
        if (is_object($updateForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$updateForm method parameter is deprecated and will be removed in future versions. Please user the form option in the config section.');
        }

        $config = array_replace_recursive($this->config['update'] ?? [], $config);
        $updateForm = $updateForm ?: $this->updateForm ?: $config['form'] ?? null;

        $entity = $request->attributes->get($this->config['entity_attribute']);

        if (empty($config)) {
            throw new \InvalidArgumentException('Update action configuration is empty');
        }

        $entity = $this->manager->getRepository()->findOneBy([$config['param_converter_key'] ?? 'id' => $entity]);

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if (!$entity) {
            throw $this->createNotFoundException('Entity not found');
        }

        if (!$updateForm instanceof FormTypeInterface && !is_string($updateForm)) {
            throw new \InvalidArgumentException(sprintf('Update form must be an instance of %s or a class name', FormTypeInterface::class));
        }

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
            return $response;
        }

        if ($updateForm instanceof FormOptionsInterface) {
            $formOptions = $updateForm->formOptions($entity, $request);
        } elseif ($updateForm instanceof FormTypeInterface && method_exists($updateForm, 'formOptions')) {
            trigger_deprecation('softspring/crudl-controller', '5.x', 'If you want to use formOptions method you must implement %s interface.', FormOptionsInterface::class);
            $formOptions = $updateForm->formOptions($entity, $request);
        } else {
            $formOptions = ['method' => 'POST'];
        }

        $formClassName = $updateForm instanceof FormTypeInterface ? get_class($updateForm) : $updateForm;

        $form = $this->createForm($formClassName, $entity, $formOptions)->handleRequest($request);

        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }

                $this->manager->saveEntity($entity);

                if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
                    return $response;
                }

                return $this->redirect(!empty($config['success_redirect_to']) ? $this->generateUrl($config['success_redirect_to'], [$this->config['entity_attribute'] => $entity]) : '/');
            } else {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            'form' => $form->createView(),
            $this->config['entity_attribute'] => $entity,
        ]);

        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData, $request));

        return $this->render($config['view'], $viewData->getArrayCopy());
    }

    /**
     * @param string|FormTypeInterface|null $deleteForm
     */
    public function delete(Request $request, $deleteForm = null, array $config = []): Response
    {
        if (is_object($deleteForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$deleteForm method parameter is deprecated and will be removed in future versions. Please user the form option in the config section.');
        }

        $config = array_replace_recursive($this->config['delete'] ?? [], $config);
        $deleteForm = $deleteForm ?: $this->deleteForm ?: $config['form'] ?? DefaultDeleteForm::class;

        $entity = $request->attributes->get($this->config['entity_attribute']);

        if (empty($config)) {
            throw new \InvalidArgumentException('Delete action configuration is empty');
        }

        $entity = $this->manager->getRepository()->findOneBy([$config['param_converter_key'] ?? 'id' => $entity]);

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if (!$entity) {
            throw $this->createNotFoundException('Entity not found');
        }

        if (!$deleteForm instanceof FormTypeInterface && !is_string($deleteForm)) {
            throw new \InvalidArgumentException(sprintf('Delete form must be an instance of %s or a class name', FormTypeInterface::class));
        }

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
            return $response;
        }

        if ($deleteForm instanceof FormOptionsInterface) {
            $formOptions = $deleteForm->formOptions($entity, $request);
        } elseif ($deleteForm instanceof FormTypeInterface && method_exists($deleteForm, 'formOptions')) {
            trigger_deprecation('softspring/crudl-controller', '5.x', 'If you want to use formOptions method you must implement %s interface.', FormOptionsInterface::class);
            $formOptions = $deleteForm->formOptions($entity, $request);
        } else {
            $formOptions = ['method' => 'POST'];
        }

        $formClassName = $deleteForm instanceof FormTypeInterface ? get_class($deleteForm) : $deleteForm;

        $form = $this->createForm($formClassName, $entity, $formOptions)->handleRequest($request);

        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }

                try {
                    $this->manager->deleteEntity($entity);

                    if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
                        return $response;
                    }

                    return $this->redirect(!empty($config['success_redirect_to']) ? $this->generateUrl($config['success_redirect_to']) : '/');
                } catch (\Exception $e) {
                    if ($response = $this->dispatchGetResponseFromConfig($config, 'delete_exception_event_name', new GetResponseEntityExceptionEvent($entity, $request, $e))) {
                        return $response;
                    }
                }
            } else {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }
            }
        }

        $this->dispatchFromConfig($config, 'view_event_name', $event = new ViewEvent([
            'form' => $form->createView(),
            $this->config['entity_attribute'] => $entity,
        ], $request));

        return $this->render($config['view'], $event->getData()->getArrayCopy());
    }

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

    protected function dispatchGetResponseFromConfig(array $config, string $eventNameKey, GetResponseEventInterface $event): ?Response
    {
        if (isset($config[$eventNameKey])) {
            if ($response = $this->dispatchGetResponse($config[$eventNameKey], $event)) {
                return $response;
            }
        }

        return null;
    }

    protected function dispatchFromConfig(array $config, string $eventNameKey, Event $event): void
    {
        if (isset($config[$eventNameKey])) {
            $this->dispatch($config[$eventNameKey], $event);
        }
    }
}
