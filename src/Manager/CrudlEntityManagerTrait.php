<?php

namespace Softspring\Component\CrudlController\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

trait CrudlEntityManagerTrait
{
    protected EntityManagerInterface $em;

    abstract public function getTargetClass(): string;

    public function getEntityClass(): string
    {
        return $this->getEntityClassReflection()->name;
    }

    public function getEntityClassReflection(): \ReflectionClass
    {
        $metadata = $this->em->getClassMetadata($this->getTargetClass());

        return $metadata->getReflectionClass();
    }

    public function getRepository(): EntityRepository
    {
        return $this->em->getRepository($this->getTargetClass());
    }

    public function createEntity(): object
    {
        $class = $this->getEntityClass();

        return new $class();
    }

    public function saveEntity(object $entity, bool $flush = true): void
    {
        if (!$this->getEntityClassReflection()->isInstance($entity)) {
            throw new \InvalidArgumentException(sprintf('$entity must be an instance of %s', $this->getEntityClass()));
        }

        $this->em->persist($entity);
        $this->em->flush();
    }

    public function deleteEntity(object $entity): void
    {
        if (!$this->getEntityClassReflection()->isInstance($entity)) {
            throw new \InvalidArgumentException(sprintf('$entity must be an instance of %s', $this->getEntityClass()));
        }

        $this->em->remove($entity);
        $this->em->flush();
    }

    public function getEntityManager(): EntityManagerInterface
    {
        return $this->em;
    }
}
