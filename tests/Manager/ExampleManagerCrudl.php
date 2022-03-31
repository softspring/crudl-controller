<?php

namespace Softspring\Component\CrudlController\Tests\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerTrait;

class ExampleManagerCrudl implements CrudlEntityManagerInterface
{
    use CrudlEntityManagerTrait;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getTargetClass(): string
    {
        return 'Softspring\\Component\\CrudlController\\Tests\\Manager\\ExampleEntity';
    }
}
