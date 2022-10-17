<?php

namespace Softspring\Component\CrudlController\Controller;

use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\Component\CrudlController\Event\GetResponseFormEvent;
use Softspring\Component\CrudlController\Form\FormOptionsInterface;
use Softspring\Component\Events\FormEvent;
use Softspring\Component\Events\ViewEvent;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait UpdateCrudlTrait
{
    /**
     * @param string|FormTypeInterface|null $updateForm
     */
    public function update(Request $request, $updateForm = null, array $config = []): Response
    {
        if (is_object($updateForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$updateForm method parameter is deprecated and will be removed in future versions. Please user the form option in the config section.');
        }

        $config = array_replace_recursive($this->config['update'] ?? [], $config);
        $updateForm = $updateForm ?: $this->updateForm ?: $config['form'] ?? null;

        $entity = $request->attributes->get($this->config['entity_attribute']);

        if (empty($config)) {
            throw new \InvalidArgumentException('Update action configuration is empty');
        }

        $entity = $this->manager->getRepository()->findOneBy([$config['param_converter_key'] ?? 'id' => $entity]);

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if (!$entity) {
            throw $this->createNotFoundException('Entity not found');
        }

        if (!$updateForm instanceof FormTypeInterface && !is_string($updateForm)) {
            throw new \InvalidArgumentException(sprintf('Update form must be an instance of %s or a class name', FormTypeInterface::class));
        }

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
            return $response;
        }

        if ($updateForm instanceof FormOptionsInterface) {
            $formOptions = $updateForm->formOptions($entity, $request);
        } elseif ($updateForm instanceof FormTypeInterface && method_exists($updateForm, 'formOptions')) {
            trigger_deprecation('softspring/crudl-controller', '5.x', 'If you want to use formOptions method you must implement %s interface.', FormOptionsInterface::class);
            $formOptions = $updateForm->formOptions($entity, $request);
        } else {
            $formOptions = ['method' => 'POST'];
        }

        $formClassName = $updateForm instanceof FormTypeInterface ? get_class($updateForm) : $updateForm;

        $form = $this->createForm($formClassName, $entity, $formOptions)->handleRequest($request);

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
