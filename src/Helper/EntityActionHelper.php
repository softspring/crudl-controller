<?php

namespace Softspring\Component\CrudlController\Helper;

use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\Component\Events\GetResponseRequestEvent;
use Symfony\Component\HttpFoundation\Response;

class EntityActionHelper extends ActionHelper
{
    protected ?object $entity = null;

    public function getEntity(): ?object
    {
        return $this->entity;
    }

    public function notFound(): bool
    {
        return !$this->entity;
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

    public function dispatchInitializeEvent(): ?Response
    {
        if (!$this->config['initialize_event_name']) {
            return null;
        }

        $event = new GetResponseEntityEvent($this->entity, $this->request);

        return $this->_dispatchGetResponse($event, $this->config['initialize_event_name']);
    }

    public function dispatchNotFoundEvent(): ?Response
    {
        if (!$this->config['not_found_event_name']) {
            return null;
        }

        $event = new GetResponseRequestEvent($this->request);

        return $this->_dispatchGetResponse($event, $this->config['not_found_event_name']);
    }

    public function createViewData(array $data = []): \ArrayObject
    {
        $data[$this->config['entity_attribute']] = $this->entity;

        return parent::createViewData($data);
    }
}
