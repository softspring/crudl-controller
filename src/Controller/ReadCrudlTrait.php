<?php

namespace Softspring\Component\CrudlController\Controller;

use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\Component\CrudlController\Exception\EmptyConfigException;
use Softspring\Component\Events\ViewEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait ReadCrudlTrait
{
    public function read(Request $request, array $config = []): Response
    {
        $config = array_replace_recursive($this->config['read'] ?? [], $config);

        if (empty($config)) {
            throw new EmptyConfigException('Read');
        }

        $entity = $request->attributes->get($this->config['entity_attribute']);

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
}
