<?php

namespace Softspring\Component\CrudlController\Event;

use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;

trait CrudlEventTrait
{
    protected CrudlEntityManagerInterface $manager;

    protected array $config;

    public function setManager(CrudlEntityManagerInterface $manager): void
    {
        $this->manager = $manager;
    }

    public function getManager(): CrudlEntityManagerInterface
    {
        return $this->manager;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
