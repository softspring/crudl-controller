<?php

namespace Softspring\Component\CrudlController\Helper;

use ArrayObject;
use Softspring\Component\CrudlController\Event\CreateEntityEvent;
use Softspring\Component\CrudlController\Event\EntityFoundEvent;
use Softspring\Component\CrudlController\Event\LoadEntityEvent;
use Softspring\Component\CrudlController\Event\NotFoundEvent;
use Symfony\Component\HttpFoundation\Response;

class EntityActionHelper extends ActionHelper
{
    protected mixed $entity = null;

    public function getEntity(): mixed
    {
        return $this->entity;
    }

    public function notFound(): bool
    {
        return null === $this->entity;
    }

    public function createEntity(): object
    {
        $this->entity = $this->manager->createEntity();

        return $this->entity;
    }

    public function findEntity(): ?object
    {
        $searchField = $this->config['param_converter_key'];
        $searchValue = $this->request->attributes->get($this->config['entity_attribute']);

        $this->entity = $this->manager->getRepository()->findOneBy([$searchField => $searchValue]);

        return $this->entity;
    }

    public function checkIsGranted(mixed $subject = null, string $message = 'Access denied, user is not %s.'): void
    {
        parent::checkIsGranted($subject ?: $this->entity, $message);
    }

    public function dispatchCreateEntityEvent(): mixed
    {
        if (!$this->config['create_entity_event_name']) {
            return null;
        }

        $event = new CreateEntityEvent(null, $this->request);
        $event->setManager($this->manager);
        $event->setConfig($this->config);
        $this->_dispatch($event, $this->config['create_entity_event_name']);

        $this->entity = $event->getEntity();

        return $event->getEntity();
    }

    public function dispatchLoadEntityEvent(): object|bool|null
    {
        if (!$this->config['load_entity_event_name']) {
            return null;
        }

        $event = new LoadEntityEvent(null, $this->request);
        $event->setManager($this->manager);
        $event->setConfig($this->config);
        $this->_dispatch($event, $this->config['load_entity_event_name']);

        $this->entity = $event->getEntity();

        return null !== $event->getEntity() ? $event->getEntity() : ($event->isNotFound() ? true : null); // if is not found, skip
    }

    public function dispatchFoundEvent(): ?Response
    {
        if (!$this->config['found_event_name']) {
            return null;
        }

        $event = new EntityFoundEvent($this->entity, $this->request);
        $event->setManager($this->manager);
        $event->setConfig($this->config);
        $response = $this->_dispatchGetResponse($event, $this->config['found_event_name']);

        $this->entity = $event->getEntity();

        return $response;
    }

    public function dispatchNotFoundEvent(): ?Response
    {
        if (!$this->config['not_found_event_name']) {
            return null;
        }

        $event = new NotFoundEvent($this->request);
        $event->setManager($this->manager);
        $event->setConfig($this->config);

        return $this->_dispatchGetResponse($event, $this->config['not_found_event_name']);
    }

    public function createViewData(array $data = []): ArrayObject
    {
        $data[$this->config['entity_attribute']] = $this->entity;

        return parent::createViewData($data);
    }
}
