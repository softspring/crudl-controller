<?php

namespace Softspring\Component\CrudlController\Controller;

use Softspring\Component\CrudlController\Form\EntityListFilterFormInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\Component\Events\DispatchGetResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * Entity CRUDL controller (CRUD+listing).
 */
class CrudlController extends AbstractController
{
    use DispatchGetResponseTrait;

    use CreateCrudlTrait;
    use ReadCrudlTrait;
    use UpdateCrudlTrait;
    use DeleteCrudlTrait;
    use ListCrudlTrait;
    use DispatchFromConfigTrait;

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
}
