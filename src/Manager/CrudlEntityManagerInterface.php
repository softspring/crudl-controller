<?php

namespace Softspring\Component\CrudlController\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

interface CrudlEntityManagerInterface
{
    public function getTargetClass(): string;

    public function getEntityClass(): string;

    public function getEntityClassReflection(): \ReflectionClass;

    public function getRepository(): EntityRepository;

    public function createEntity(): object;

    public function saveEntity(object $entity): void;

    public function deleteEntity(object $entity): void;

    public function getEntityManager(): EntityManagerInterface;
}
