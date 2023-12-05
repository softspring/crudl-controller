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
use Symfony\Component\Form\FormFactory;
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
        protected FormFactory $formFactory,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected RouterInterface $router,
        protected array $configs = [],
    ) {
    }

    /** @noinspection DuplicatedCode */
    public function create(Request $request, array $config = [], string $configKey = 'create'): Response
    {
        // create helper
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::createAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        // init entity
        $helper->createEntity();

        // init action
        $helper->checkIsGranted();

        if ($response = $helper->dispatchInitializeEvent()) {
            return $response;
        }

        // create form
        $formPrepareEvent = $helper->dispatchFormPrepare();
        $form = $helper->createForm($formPrepareEvent);
        $helper->dispatchFormInit();

        // process form
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($response = $helper->dispatchFormValid()) {
                    return $response;
                }

                try {
                    $this->manager->saveEntity($helper->getEntity());

                    if ($response = $helper->dispatchSuccess()) {
                        return $response;
                    }

                    return $helper->successRedirect();
                } catch (\Exception $e) {
                    if ($response = $helper->dispatchException($e)) {
                        return $response;
                    }
                }
            } else {
                if ($response = $helper->dispatchFormInvalid()) {
                    return $response;
                }
            }
        }

        // create and render view
        $helper->createViewData();
        $helper->dispatchViewEvent();

        return $helper->renderResponse();
    }

    public function read(Request $request, array $config = [], string $configKey = 'read'): Response
    {
        // create helper
        $helper = new EntityActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router);
        $helper->setConfig(Configuration::readAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        // init entity
        $helper->findEntity();

        // init action
        $helper->checkIsGranted();

        if ($helper->notFound()) {
            if ($response = $helper->dispatchNotFoundEvent()) {
                return $response;
            }

            throw new NotFoundHttpException('Entity not found');
        }

        if ($response = $helper->dispatchInitializeEvent()) {
            return $response;
        }

        // create and render view
        $helper->createViewData();
        $helper->dispatchViewEvent();

        return $helper->renderResponse();
    }

    /** @noinspection DuplicatedCode */
    public function update(Request $request, array $config = [], string $configKey = 'update'): Response
    {
        // update helper
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::updateAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        // init entity
        $helper->findEntity();

        // init action
        $helper->checkIsGranted();

        if ($helper->notFound()) {
            if ($response = $helper->dispatchNotFoundEvent()) {
                return $response;
            }

            throw new NotFoundHttpException('Entity not found');
        }

        if ($response = $helper->dispatchInitializeEvent()) {
            return $response;
        }

        // create form
        $formPrepareEvent = $helper->dispatchFormPrepare();
        $form = $helper->createForm($formPrepareEvent);
        $helper->dispatchFormInit();

        // process form
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($response = $helper->dispatchFormValid()) {
                    return $response;
                }

                try {
                    $this->manager->saveEntity($helper->getEntity());

                    if ($response = $helper->dispatchSuccess()) {
                        return $response;
                    }

                    return $helper->successRedirect();
                } catch (\Exception $e) {
                    if ($response = $helper->dispatchException($e)) {
                        return $response;
                    }
                }
            } else {
                if ($response = $helper->dispatchFormInvalid()) {
                    return $response;
                }
            }
        }

        // create and render view
        $helper->createViewData();
        $helper->dispatchViewEvent();

        return $helper->renderResponse();
    }

    /** @noinspection DuplicatedCode */
    public function delete(Request $request, array $config = [], string $configKey = 'delete'): Response
    {
        // delete helper
        $helper = new FormActionActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::deleteAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        // init entity
        $helper->findEntity();

        // init action
        $helper->checkIsGranted();

        if ($helper->notFound()) {
            if ($response = $helper->dispatchNotFoundEvent()) {
                return $response;
            }

            throw new NotFoundHttpException('Entity not found');
        }

        if ($response = $helper->dispatchInitializeEvent()) {
            return $response;
        }

        // create form
        $formPrepareEvent = $helper->dispatchFormPrepare();
        $form = $helper->createForm($formPrepareEvent);
        $helper->dispatchFormInit();

        // process form
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($response = $helper->dispatchFormValid()) {
                    return $response;
                }

                try {
                    $this->manager->deleteEntity($helper->getEntity());

                    if ($response = $helper->dispatchSuccess()) {
                        return $response;
                    }

                    return $helper->successRedirect();
                } catch (\Exception $e) {
                    if ($response = $helper->dispatchException($e)) {
                        return $response;
                    }
                }
            } else {
                if ($response = $helper->dispatchFormInvalid()) {
                    return $response;
                }
            }
        }

        // create and render view
        $helper->createViewData();
        $helper->dispatchViewEvent();

        return $helper->renderResponse();
    }

    /**
     * @throws InvalidFormTypeException
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws InvalidFilterValueException
     * @throws MissingFromInQueryBuilderException
     */
    public function list(Request $request, array $config = [], string $configKey = 'list'): Response
    {
        // list helper
        $helper = new ListActionHelper($this->manager, $this->eventDispatcher, $this->twig, $this->authorizationChecker, $this->router, $this->formFactory);
        $helper->setConfig(Configuration::listAction($configKey, $this->configs, $config));
        $helper->setRequest($request);

        // init action
        $helper->checkIsGranted();
        if ($response = $helper->dispatchInitializeEvent()) {
            return $response;
        }

        $helper->createFilterForm();
        $helper->dispatchResultFilterEvent();
        $helper->queryResults();

        // create and render view
        $helper->createViewData();
        $helper->dispatchViewEvent();

        return $helper->renderResponse();
    }
}
