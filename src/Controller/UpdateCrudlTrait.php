<?php

namespace Softspring\Component\CrudlController\Controller;

use Softspring\Component\CrudlController\Event\FormPrepareEvent;
use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\Component\CrudlController\Event\GetResponseFormEvent;
use Softspring\Component\CrudlController\Exception\EmptyConfigException;
use Softspring\Component\CrudlController\Exception\InvalidFormException;
use Softspring\Component\Events\FormEvent;
use Softspring\Component\Events\ViewEvent;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait UpdateCrudlTrait
{
    public function update(Request $request, array $config = [], string $configKey = 'update'): Response
    {
        $config = array_replace_recursive($this->config[$configKey] ?? [], $config);

        if (empty($config)) {
            throw new EmptyConfigException('Update');
        }

        $updateForm = $config['form'] ?? null;

        $entity = $request->attributes->get($this->config['entity_attribute']);

        $entity = $this->manager->getRepository()->findOneBy([$config['param_converter_key'] ?? 'id' => $entity]);

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if (!$entity) {
            throw $this->createNotFoundException('Entity not found');
        }

        if (!$updateForm instanceof FormTypeInterface && !is_string($updateForm)) {
            throw new InvalidFormException('Update');
        }

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
            return $response;
        }

        $this->dispatchFromConfig($config, 'form_prepare_event_name', $formPrepareEvent = new FormPrepareEvent($entity, $request, ['method' => 'POST']));
        $formClassName = $updateForm instanceof FormTypeInterface ? get_class($updateForm) : $updateForm;
        $form = $this->createForm($formClassName, $entity, $formPrepareEvent->getFormOptions())->handleRequest($request);

        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }

                $this->manager->saveEntity($entity);

                if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
                    return $response;
                }

                return $this->redirect(!empty($config['success_redirect_to']) ? $this->generateUrl($config['success_redirect_to'], [$this->config['entity_attribute'] => $entity]) : '/');
            } else {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            'form' => $form->createView(),
            $this->config['entity_attribute'] => $entity,
        ]);

        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData, $request));

        return $this->render($config['view'], $viewData->getArrayCopy());
    }
}
