<?php

namespace Softspring\Component\CrudlController\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

interface CrudlEntityManagerInterface
{
    /**
     * Returns the entity target class name (could be the entity class or a target class).
     */
    public function getTargetClass(): string;

    /**
     * Returns the entity class name (always the entity class).
     */
    public function getEntityClass(): string;

    /**
     * Returns the class reflection of the doctrine managed entity.
     */
    public function getEntityClassReflection(): \ReflectionClass;

    /**
     * Returns the entity repository.
     */
    public function getRepository(): EntityRepository;

    /**
     * Creates a new entity instance.
     */
    public function createEntity(): object;

    /**
     * Saves the entity.
     */
    public function saveEntity(object $entity): void;

    /**
     * Deletes the entity.
     */
    public function deleteEntity(object $entity): void;

    /**
     * Returns the doctrine EntityManager for this entity.
     */
    public function getEntityManager(): EntityManagerInterface;
}
