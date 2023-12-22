<?php

namespace Softspring\Component\CrudlController\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Softspring\Component\CrudlController\Config\Configuration;
use Softspring\Component\CrudlController\Helper\EntityActionHelper;
use Softspring\Component\CrudlController\Helper\FormActionActionHelper;
use Softspring\Component\CrudlController\Helper\ListActionHelper;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\Component\DoctrinePaginator\Exception\InvalidFormTypeException;
use Softspring\Component\DoctrineQueryFilters\Exception\InvalidFilterValueException;
use Softspring\Component\DoctrineQueryFilters\Exception\MissingFromInQueryBuilderException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class CrudlController
{
    public function __construct(
        protected CrudlEntityManagerInterface $manager,
        protected EventDispatcherInterface $eventDispatcher,
        protected Environment $twig,
        protected FormFactoryInterface $formFactory,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected RouterInterface $router,
        protected array $config = [],
        protected array $configs = [],
    ) {
        if (!empty($config)) {
            trigger_deprecation('softspring/crudl-controller', '5.2', 'Passing $config argument to CrudlController constructor is deprecated, use $configs instead');
            $this->configs = $this->configs ?: $config;
        }

        if ($this->configs['entity_attribute'] ?? false) {
            foreach ($this->configs as $configName => $actionConfig) {
                if (is_array($actionConfig)) {
                    // if not defined, use global entity_attribute
                    $this->configs[$configName]['entity_attribute'] = $actionConfig['entity_attribute'] ?? $this->configs['entity_attribute'];
                }
            }
        }
    }

    /**
     * @noinspection DuplicatedCode
     * @throws \Exception
     */
    public function create(Request $request, array $config = [], string $configKey = 'create'): Response
    {
        // create helper
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::createAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        try {
            if ($response = $helper->dispatchInitialize()) {
                return $response;
            }

            // init entity
            if (!$helper->dispatchCreateEntityEvent()) {
                $helper->createEntity();
            }

            // init action
            $helper->checkIsGranted();

            $formPrepareEvent = $helper->dispatchFormPrepare();
            $form = $helper->createForm($formPrepareEvent);
            $helper->dispatchFormInit();

            // process form
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    if ($response = $helper->dispatchFormValid()) {
                        return $response;
                    }

                    if ($response = $this->helperApply($helper, function ($entity) {
                        $this->manager->saveEntity($entity);
                    })) {
                        return $response;
                    }
                } else {
                    if ($response = $helper->dispatchFormInvalid()) {
                        return $response;
                    }
                }
            }

            // create and render view
            $helper->createViewData();
            $viewEvent = $helper->dispatchViewEvent();

            return $helper->renderResponse($viewEvent);
        } catch (\Exception $e) {
            if ($response = $helper->dispatchException($e)) {
                return $response;
            }

            throw $e;
        }
    }

    /**
     * @noinspection DuplicatedCode
     * @throws \Exception
     */
    public function read(Request $request, array $config = [], string $configKey = 'read'): Response
    {
        // create helper
        $helper = new EntityActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router);
        $helper->setConfig(Configuration::readAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        try {
            if ($response = $helper->dispatchInitialize()) {
                return $response;
            }

            // init entity
            if (!$helper->dispatchLoadEntityEvent()) {
                $helper->findEntity();
            }

            // init action
            $helper->checkIsGranted();

            if ($helper->notFound()) {
                if ($response = $helper->dispatchNotFoundEvent()) {
                    return $response;
                }

                throw new NotFoundHttpException('Entity not found');
            } else {
                if ($response = $helper->dispatchFoundEvent()) {
                    return $response;
                }
            }

            // create and render view
            $helper->createViewData();
            $viewEvent = $helper->dispatchViewEvent();

            return $helper->renderResponse($viewEvent);
        } catch (\Exception $e) {
            if ($response = $helper->dispatchException($e)) {
                return $response;
            }

            throw $e;
        }
    }

    /**
     * @noinspection DuplicatedCode
     * @throws \Exception
     */
    public function update(Request $request, array $config = [], string $configKey = 'update'): Response
    {
        // update helper
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::updateAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        try {
            if ($response = $helper->dispatchInitialize()) {
                return $response;
            }

            // init entity
            if (!$helper->dispatchLoadEntityEvent()) {
                $helper->findEntity();
            }

            // init action
            $helper->checkIsGranted();

            if ($helper->notFound()) {
                if ($response = $helper->dispatchNotFoundEvent()) {
                    return $response;
                }

                throw new NotFoundHttpException('Entity not found');
            } else {
                if ($response = $helper->dispatchFoundEvent()) {
                    return $response;
                }
            }

            $formPrepareEvent = $helper->dispatchFormPrepare();
            $form = $helper->createForm($formPrepareEvent);
            $helper->dispatchFormInit();

            // process form
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    if ($response = $helper->dispatchFormValid()) {
                        return $response;
                    }

                    if ($response = $this->helperApply($helper, function ($entity) {
                        $this->manager->saveEntity($entity);
                    })) {
                        return $response;
                    }
                } else {
                    if ($response = $helper->dispatchFormInvalid()) {
                        return $response;
                    }
                }
            }

            // create and render view
            $helper->createViewData();
            $viewEvent = $helper->dispatchViewEvent();

            return $helper->renderResponse($viewEvent);
        } catch (\Exception $e) {
            if ($response = $helper->dispatchException($e)) {
                return $response;
            }

            throw $e;
        }
    }

    /**
     * @noinspection DuplicatedCode
     * @throws \Exception
     */
    public function delete(Request $request, array $config = [], string $configKey = 'delete'): Response
    {
        // delete helper
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::deleteAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        try {
            if ($response = $helper->dispatchInitialize()) {
                return $response;
            }

            // init entity
            if (!$helper->dispatchLoadEntityEvent()) {
                $helper->findEntity();
            }

            // init action
            $helper->checkIsGranted();

            if ($helper->notFound()) {
                if ($response = $helper->dispatchNotFoundEvent()) {
                    return $response;
                }

                throw new NotFoundHttpException('Entity not found');
            } else {
                if ($response = $helper->dispatchFoundEvent()) {
                    return $response;
                }
            }

            $formPrepareEvent = $helper->dispatchFormPrepare();
            $form = $helper->createForm($formPrepareEvent);
            $helper->dispatchFormInit();

            // process form
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    if ($response = $helper->dispatchFormValid()) {
                        return $response;
                    }

                    if ($response = $this->helperApply($helper, function ($entity) {
                        $this->manager->deleteEntity($entity);
                    })) {
                        return $response;
                    }
                } else {
                    if ($response = $helper->dispatchFormInvalid()) {
                        return $response;
                    }
                }
            }

            // create and render view
            $helper->createViewData();
            $viewEvent = $helper->dispatchViewEvent();

            return $helper->renderResponse($viewEvent);
        } catch (\Exception $e) {
            if ($response = $helper->dispatchException($e)) {
                return $response;
            }

            throw $e;
        }
    }

    /**
     * @throws InvalidFormTypeException
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws InvalidFilterValueException
     * @throws MissingFromInQueryBuilderException
     * @throws \Exception
     */
    public function list(Request $request, array $config = [], string $configKey = 'list'): Response
    {
        // list helper
        $helper = new ListActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::listAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        try {
            // init action
            if ($response = $helper->dispatchInitialize()) {
                return $response;
            }

            $helper->checkIsGranted();

            $formPrepareEvent = $helper->dispatchFormPrepare();
            $helper->createFilterForm($formPrepareEvent);
            $helper->dispatchFormInit();
            $helper->dispatchResultFilterEvent();
            $helper->queryResults();

            // create and render view
            $helper->createViewData();
            $viewEvent = $helper->dispatchViewEvent();

            return $helper->renderResponse($viewEvent);
        } catch (\Exception $e) {
            if ($response = $helper->dispatchException($e)) {
                return $response;
            }

            throw $e;
        }
    }

    /**
     * @noinspection DuplicatedCode
     * @throws \Exception
     */
    public function apply(Request $request, string $configKey, array $config = []): Response
    {
        // update helper
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::actionAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        try {
            if ($response = $helper->dispatchInitialize()) {
                return $response;
            }

            // init entity
            if (!$helper->dispatchLoadEntityEvent()) {
                $helper->findEntity();
            }

            // init action
            $helper->checkIsGranted();

            if ($helper->notFound()) {
                if ($response = $helper->dispatchNotFoundEvent()) {
                    return $response;
                }

                throw new NotFoundHttpException('Entity not found');
            } else {
                if ($response = $helper->dispatchFoundEvent()) {
                    return $response;
                }
            }

            if ($response = $this->helperApply($helper, function ($entity) {
                throw new \Exception('Apply action must use apply event and set it to applied');
            })) {
                return $response;
            }

            throw new \Exception('Apply action must return a response in success or failure events');
        } catch (\Exception $e) {
            if ($response = $helper->dispatchException($e)) {
                return $response;
            }

            throw $e;
        }
    }

    protected function helperApply(FormActionActionHelper $helper, callable $applyFunction): ?Response
    {
        try {
            if (!$helper->dispatchApplyEvent()) {
                $applyFunction($helper->getEntity());
            }

            if ($response = $helper->dispatchSuccess()) {
                return $response;
            }

            return $helper->successRedirect();
        } catch (\Exception $e) {
            if ($response = $helper->dispatchFailure($e)) {
                return $response;
            }
        }

        return null;
    }
}
