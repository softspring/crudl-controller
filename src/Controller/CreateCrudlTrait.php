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

trait CreateCrudlTrait
{
    /**
     * @param string|FormTypeInterface|null $createForm
     */
    public function create(Request $request, $createForm = null, array $config = []): Response
    {
        if (is_object($createForm)) {
            trigger_deprecation('softspring/crudl-controller', '5.x', '$createForm method parameter is deprecated and will be removed in future versions. Please user the form option in the config section.');
        }

        $config = array_replace_recursive($this->config['create'] ?? [], $config);
        $createForm = $createForm ?: $this->createForm ?: $config['form'] ?? null;

        if (empty($config)) {
            throw new \InvalidArgumentException('Create action configuration is empty');
        }

        if (!empty($config['is_granted'])) {
            $this->denyAccessUnlessGranted($config['is_granted'], null, sprintf('Access denied, user is not %s.', $config['is_granted']));
        }

        if (!$createForm instanceof FormTypeInterface && !is_string($createForm)) {
            throw new \InvalidArgumentException(sprintf('Create form must be an instance of %s or a class name', FormTypeInterface::class));
        }

        $entity = $this->manager->createEntity();

        if ($response = $this->dispatchGetResponseFromConfig($config, 'initialize_event_name', new GetResponseEntityEvent($entity, $request))) {
            return $response;
        }

        if ($createForm instanceof FormOptionsInterface) {
            $formOptions = $createForm->formOptions($entity, $request);
        } elseif ($createForm instanceof FormTypeInterface && method_exists($createForm, 'formOptions')) {
            trigger_deprecation('softspring/crudl-controller', '5.x', 'If you want to use formOptions method you must implement %s interface.', FormOptionsInterface::class);
            $formOptions = $createForm->formOptions($entity, $request);
        } else {
            $formOptions = ['method' => 'POST'];
        }

        $formClassName = $createForm instanceof FormTypeInterface ? get_class($createForm) : $createForm;

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
