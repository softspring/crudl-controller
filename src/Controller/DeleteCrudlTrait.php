<?php

namespace Softspring\Component\CrudlController\Controller;

use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\Component\CrudlController\Event\GetResponseEntityExceptionEvent;
use Softspring\Component\CrudlController\Event\GetResponseFormEvent;
use Softspring\Component\CrudlController\Form\DefaultDeleteForm;
use Softspring\Component\CrudlController\Form\FormOptionsInterface;
use Softspring\Component\Events\FormEvent;
use Softspring\Component\Events\ViewEvent;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait DeleteCrudlTrait
{
    /**
     * @param string|FormTypeInterface|null $deleteForm
     */
    public function delete(Request $request, $deleteForm = null, array $config = []): Response
    {
        if (is_object($deleteForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$deleteForm method parameter is deprecated and will be removed in future versions. Please user the form option in the config section.');
        }

        $config = array_replace_recursive($this->config['delete'] ?? [], $config);
        $deleteForm = $deleteForm ?: $this->deleteForm ?: $config['form'] ?? DefaultDeleteForm::class;

        $entity = $request->attributes->get($this->config['entity_attribute']);

        if (empty($config)) {
            throw new \InvalidArgumentException('Delete action configuration is empty');
        }

        $entity = $this->manager->getRepository()->findOneBy([$config['param_converter_key'] ?? 'id' => $entity]);

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], $entity, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if (!$entity) {
            throw $this->createNotFoundException('Entity not found');
        }

        if (!$deleteForm instanceof FormTypeInterface && !is_string($deleteForm)) {
            throw new \InvalidArgumentException(sprintf('Delete form must be an instance of %s or a class name', FormTypeInterface::class));
        }

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
            return $response;
        }

        if ($deleteForm instanceof FormOptionsInterface) {
            $formOptions = $deleteForm->formOptions($entity, $request);
        } elseif ($deleteForm instanceof FormTypeInterface && method_exists($deleteForm, 'formOptions')) {
            trigger_deprecation('softspring/crudl-controller', '5.x', 'If you want to use formOptions method you must implement %s interface.', FormOptionsInterface::class);
            $formOptions = $deleteForm->formOptions($entity, $request);
        } else {
            $formOptions = ['method' => 'POST'];
        }

        $formClassName = $deleteForm instanceof FormTypeInterface ? get_class($deleteForm) : $deleteForm;

        $form = $this->createForm($formClassName, $entity, $formOptions)->handleRequest($request);

        $this->dispatchFromConfig($config, 'form_init_event_name', new FormEvent($form, $request));

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_valid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }

                try {
                    $this->manager->deleteEntity($entity);

                    if ($response = $this->dispatchGetResponseFromConfig($config, 'success_event_name', new GetResponseEntityEvent($entity, $request))) {
                        return $response;
                    }

                    return $this->redirect(!empty($config['success_redirect_to']) ? $this->generateUrl($config['success_redirect_to']) : '/');
                } catch (\Exception $e) {
                    if ($response = $this->dispatchGetResponseFromConfig($config, 'delete_exception_event_name', new GetResponseEntityExceptionEvent($entity, $request, $e))) {
                        return $response;
                    }
                }
            } else {
                if ($response = $this->dispatchGetResponseFromConfig($config, 'form_invalid_event_name', new GetResponseFormEvent($form, $request))) {
                    return $response;
                }
            }
        }

        $this->dispatchFromConfig($config, 'view_event_name', $event = new ViewEvent([
            'form' => $form->createView(),
            $this->config['entity_attribute'] => $entity,
        ], $request));

        return $this->render($config['view'], $event->getData()->getArrayCopy());
    }
}
