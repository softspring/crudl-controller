<?php

namespace Softspring\Component\CrudlController\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Softspring\Component\CrudlController\Config\Configuration;
use Softspring\Component\CrudlController\Helper\EntityActionHelper;
use Softspring\Component\CrudlController\Helper\FormActionActionHelper;
use Softspring\Component\CrudlController\Helper\ListActionHelper;
use Softspring\Component\CrudlController\Helper\TransitionActionHelper;
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
use Symfony\Component\Workflow\Registry;
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
        protected ?Registry $registry = null,
    ) {
        if ([] !== $config) {
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

    protected function buildCreateActionHelper(Request $request, array $config, string $configKey): FormActionActionHelper
    {
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::createAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        return $helper;
    }

    /**
     * @noinspection DuplicatedCode
     * @throws Exception
     */
    public function create(Request $request, array $config = [], string $configKey = 'create'): Response
    {
        $helper = $this->buildCreateActionHelper($request, $config, $configKey);

        try {
            if (($response = $helper->dispatchInitialize()) instanceof Response) {
                return $response;
            }

            // init entity
            if (null === $helper->dispatchCreateEntityEvent()) {
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
                    if (($response = $helper->dispatchFormValid()) instanceof Response) {
                        return $response;
                    }
                    if (($response = $this->helperApply($helper, function (object $entity): void {
                        $this->manager->saveEntity($entity);
                    })) instanceof Response) {
                        return $response;
                    }
                } elseif (($response = $helper->dispatchFormInvalid()) instanceof Response) {
                    return $response;
                }
            }

            // create and render view
            $helper->createViewData();
            $viewEvent = $helper->dispatchViewEvent();

            return $helper->renderResponse($viewEvent);
        } catch (Exception $e) {
            if (($response = $helper->dispatchException($e)) instanceof Response) {
                return $response;
            }

            throw $e;
        }
    }

    protected function buildReadActionHelper(Request $request, array $config, string $configKey): EntityActionHelper
    {
        $helper = new EntityActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router);
        $helper->setConfig(Configuration::readAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        return $helper;
    }

    /**
     * @noinspection DuplicatedCode
     * @throws Exception
     */
    public function read(Request $request, array $config = [], string $configKey = 'read'): Response
    {
        $helper = $this->buildReadActionHelper($request, $config, $configKey);

        try {
            if (($response = $helper->dispatchInitialize()) instanceof Response) {
                return $response;
            }

            // init entity
            if (!$helper->dispatchLoadEntityEvent()) {
                $helper->findEntity();
            }

            // init action
            $helper->checkIsGranted();

            if ($helper->notFound()) {
                if (($response = $helper->dispatchNotFoundEvent()) instanceof Response) {
                    return $response;
                }
                throw new NotFoundHttpException('Entity not found');
            } elseif (($response = $helper->dispatchFoundEvent()) instanceof Response) {
                return $response;
            }

            // create and render view
            $helper->createViewData();
            $viewEvent = $helper->dispatchViewEvent();

            return $helper->renderResponse($viewEvent);
        } catch (Exception $e) {
            if (($response = $helper->dispatchException($e)) instanceof Response) {
                return $response;
            }

            throw $e;
        }
    }

    protected function buildUpdateActionHelper(Request $request, array $config, string $configKey): FormActionActionHelper
    {
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::updateAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        return $helper;
    }

    /**
     * @noinspection DuplicatedCode
     * @throws Exception
     */
    public function update(Request $request, array $config = [], string $configKey = 'update'): Response
    {
        $helper = $this->buildUpdateActionHelper($request, $config, $configKey);

        try {
            if (($response = $helper->dispatchInitialize()) instanceof Response) {
                return $response;
            }

            // init entity
            if (!$helper->dispatchLoadEntityEvent()) {
                $helper->findEntity();
            }

            // init action
            $helper->checkIsGranted();

            if ($helper->notFound()) {
                if (($response = $helper->dispatchNotFoundEvent()) instanceof Response) {
                    return $response;
                }
                throw new NotFoundHttpException('Entity not found');
            } elseif (($response = $helper->dispatchFoundEvent()) instanceof Response) {
                return $response;
            }

            $formPrepareEvent = $helper->dispatchFormPrepare();
            $form = $helper->createForm($formPrepareEvent);
            $helper->dispatchFormInit();

            // process form
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    if (($response = $helper->dispatchFormValid()) instanceof Response) {
                        return $response;
                    }
                    if (($response = $this->helperApply($helper, function (object $entity): void {
                        $this->manager->saveEntity($entity);
                    })) instanceof Response) {
                        return $response;
                    }
                } elseif (($response = $helper->dispatchFormInvalid()) instanceof Response) {
                    return $response;
                }
            }

            // create and render view
            $helper->createViewData();
            $viewEvent = $helper->dispatchViewEvent();

            return $helper->renderResponse($viewEvent);
        } catch (Exception $e) {
            if (($response = $helper->dispatchException($e)) instanceof Response) {
                return $response;
            }

            throw $e;
        }
    }

    protected function buildDeleteActionHelper(Request $request, array $config, string $configKey): FormActionActionHelper
    {
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::deleteAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        return $helper;
    }

    /**
     * @noinspection DuplicatedCode
     * @throws Exception
     */
    public function delete(Request $request, array $config = [], string $configKey = 'delete'): Response
    {
        $helper = $this->buildDeleteActionHelper($request, $config, $configKey);

        try {
            if (($response = $helper->dispatchInitialize()) instanceof Response) {
                return $response;
            }

            // init entity
            if (!$helper->dispatchLoadEntityEvent()) {
                $helper->findEntity();
            }

            // init action
            $helper->checkIsGranted();

            if ($helper->notFound()) {
                if (($response = $helper->dispatchNotFoundEvent()) instanceof Response) {
                    return $response;
                }
                throw new NotFoundHttpException('Entity not found');
            } elseif (($response = $helper->dispatchFoundEvent()) instanceof Response) {
                return $response;
            }

            $formPrepareEvent = $helper->dispatchFormPrepare();
            $form = $helper->createForm($formPrepareEvent);
            $helper->dispatchFormInit();

            // process form
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    if (($response = $helper->dispatchFormValid()) instanceof Response) {
                        return $response;
                    }
                    if (($response = $this->helperApply($helper, function (object $entity): void {
                        $this->manager->deleteEntity($entity);
                    })) instanceof Response) {
                        return $response;
                    }
                } elseif (($response = $helper->dispatchFormInvalid()) instanceof Response) {
                    return $response;
                }
            }

            // create and render view
            $helper->createViewData();
            $viewEvent = $helper->dispatchViewEvent();

            return $helper->renderResponse($viewEvent);
        } catch (Exception $e) {
            if (($response = $helper->dispatchException($e)) instanceof Response) {
                return $response;
            }

            throw $e;
        }
    }

    protected function buildListActionHelper(Request $request, array $config, string $configKey): ListActionHelper
    {
        $helper = new ListActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::listAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        return $helper;
    }

    /**
     * @throws InvalidFormTypeException
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws InvalidFilterValueException
     * @throws MissingFromInQueryBuilderException
     * @throws Exception
     */
    public function list(Request $request, array $config = [], string $configKey = 'list'): Response
    {
        $helper = $this->buildListActionHelper($request, $config, $configKey);

        try {
            // init action
            if (($response = $helper->dispatchInitialize()) instanceof Response) {
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
        } catch (Exception $e) {
            if (($response = $helper->dispatchException($e)) instanceof Response) {
                return $response;
            }

            throw $e;
        }
    }

    protected function buildApplyActionHelper(Request $request, string $configKey, array $config): FormActionActionHelper
    {
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::actionAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        return $helper;
    }

    /**
     * @noinspection DuplicatedCode
     * @throws Exception
     */
    public function apply(Request $request, string $configKey, array $config = []): Response
    {
        $helper = $this->buildApplyActionHelper($request, $configKey, $config);

        try {
            if (($response = $helper->dispatchInitialize()) instanceof Response) {
                return $response;
            }

            // init entity
            if (!$helper->dispatchLoadEntityEvent()) {
                $helper->findEntity();
            }

            // init action
            $helper->checkIsGranted();

            if ($helper->notFound()) {
                if (($response = $helper->dispatchNotFoundEvent()) instanceof Response) {
                    return $response;
                }
                throw new NotFoundHttpException('Entity not found');
            } elseif (($response = $helper->dispatchFoundEvent()) instanceof Response) {
                return $response;
            }

            if (($response = $this->helperApply($helper, function ($entity): void {
                throw new Exception('Apply action must use apply event and set it to applied');
            })) instanceof Response) {
                return $response;
            }

            throw new Exception('Apply action must return a response in success or failure events');
        } catch (Exception $e) {
            if (($response = $helper->dispatchException($e)) instanceof Response) {
                return $response;
            }

            throw $e;
        }
    }

    protected function buildTransitionActionHelper(Request $request, array $config, string $configKey): TransitionActionHelper
    {
        $helper = new TransitionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory, $this->registry);
        $helper->setConfig(Configuration::transitionAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        return $helper;
    }

    /**
     * @noinspection DuplicatedCode
     * @throws Exception
     */
    public function transition(Request $request, array $config = [], string $configKey = 'transition'): Response
    {
        $helper = $this->buildTransitionActionHelper($request, $config, $configKey);

        try {
            $helper->initialize();

            if (($response = $helper->dispatchInitialize()) instanceof Response) {
                return $response;
            }

            // init entity
            if (!$helper->dispatchLoadEntityEvent()) {
                $helper->findEntity();
            }

            // init action
            $helper->checkIsGranted();

            if ($helper->notFound()) {
                if (($response = $helper->dispatchNotFoundEvent()) instanceof Response) {
                    return $response;
                }
                throw new NotFoundHttpException('Entity not found');
            } elseif (($response = $helper->dispatchFoundEvent()) instanceof Response) {
                return $response;
            }

            $helper->initTransition();
            $helper->checkCanTransition();

            $formPrepareEvent = $helper->dispatchFormPrepare();
            $form = $helper->createForm($formPrepareEvent);
            if ($form) {
                $helper->dispatchFormInit();
                // process form
                if ($form->isSubmitted()) {
                    if ($form->isValid()) {
                        if (($response = $helper->dispatchFormValid()) instanceof Response) {
                            return $response;
                        }
                        if (($response = $this->helperApply($helper, fn () => $helper->applyTransition())) instanceof Response) {
                            return $response;
                        }
                    } elseif (($response = $helper->dispatchFormInvalid()) instanceof Response) {
                        return $response;
                    }
                }
            } elseif (($response = $this->helperApply($helper, fn () => $helper->applyTransition())) instanceof Response) {
                return $response;
            }

            // create and render view
            $helper->createViewData();
            $viewEvent = $helper->dispatchViewEvent();

            return $helper->renderResponse($viewEvent);
        } catch (Exception $e) {
            if (($response = $helper->dispatchException($e)) instanceof Response) {
                return $response;
            }

            throw $e;
        }
    }

    /**
     * @throws Exception
     */
    protected function helperApply(FormActionActionHelper $helper, callable $applyFunction): ?Response
    {
        try {
            if (!$helper->dispatchApplyEvent()) {
                $applyFunction($helper->getEntity());
            }

            if (($response = $helper->dispatchSuccess()) instanceof Response) {
                return $response;
            }

            return $helper->successRedirect();
        } catch (Exception $e) {
            if (($response = $helper->dispatchFailure($e)) instanceof Response) {
                return $response;
            }

            throw $e; // rethrow the exception to be handled by the caller, instead of returning null
        }
    }
}
