<?php

namespace Softspring\Component\CrudlController\Controller;

use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\Component\Events\DispatchGetResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    protected array $config;

    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(CrudlEntityManagerInterface $manager, EventDispatcherInterface $eventDispatcher, array $config = [])
    {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
        $this->config = $config;
        $this->config['entity_attribute'] = $this->config['entity_attribute'] ?? 'entity';
    }
}
