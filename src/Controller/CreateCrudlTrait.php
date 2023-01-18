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

trait CreateCrudlTrait
{
    public function create(Request $request, array $config = [], string $configKey = 'create'): Response
    {
        $config = array_replace_recursive($this->config[$configKey] ?? [], $config);

        if (empty($config)) {
            throw new EmptyConfigException('Create');
        }

        $createForm = $config['form'] ?? null;

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if (!$createForm instanceof FormTypeInterface && !is_string($createForm)) {
            throw new InvalidFormException('Create');
        }

        $entity = $this->manager->createEntity();

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
            return $response;
        }

        $this->dispatchFromConfig($config, 'form_prepare_event_name', $formPrepareEvent = new FormPrepareEvent($entity, $request, ['method' => 'POST']));
        $formClassName = $createForm instanceof FormTypeInterface ? get_class($createForm) : $createForm;
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

                return $this->redirect(!empty($config['success_redirect_to']) ? $this->generateUrl($config['success_redirect_to']) : '/');
            } else {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            $this->config['entity_attribute'] => $entity,
            'form' => $form->createView(),
        ]);

        $this->dispatchFromConfig($config, 'view_event_name', new ViewEvent($viewData, $request));

        return $this->render($config['view'], $viewData->getArrayCopy());
    }
}
