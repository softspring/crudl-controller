<?php

namespace Softspring\Component\CrudlController\Helper;

use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\Component\Events\GetResponseRequestEvent;
use Softspring\Component\Events\ViewEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

abstract class ActionHelper
{
    protected array $config = [];
    protected Request $request;
    protected \ArrayObject $viewData;

    public function __construct(
        protected CrudlEntityManagerInterface $manager,
        protected EventDispatcherInterface $eventDispatcher,
        protected Environment $twig,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected RouterInterface $router
    ) {
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @throws AccessDeniedException
     */
    public function checkIsGranted(mixed $subject = null, string $message = 'Access denied, user is not %s.'): void
    {
        if (!$this->config['is_granted']) {
            return;
        }

        $attribute = $this->config['is_granted'];

        if (!$this->authorizationChecker->isGranted($attribute, $subject)) {
            $exception = new AccessDeniedException(sprintf($message, $attribute));
            $exception->setAttributes([$attribute]);
            $exception->setSubject($subject);

            throw $exception;
        }
    }

    public function renderResponse(string $view = null): Response
    {
        return new Response($this->twig->render($view ?: $this->config['view'], $this->viewData->getArrayCopy()));
    }

    public function dispatchInitializeEvent(): ?Response
    {
        if (!$this->config['initialize_event_name']) {
            return null;
        }

        $event = new GetResponseRequestEvent($this->request);

        return $this->_dispatchGetResponse($event, $this->config['initialize_event_name']);
    }

    public function createViewData(array $data = []): \ArrayObject
    {
        $this->viewData = new \ArrayObject($data);

        return $this->viewData;
    }

    public function dispatchViewEvent(): void
    {
        if (!$this->config['view_event_name']) {
            return;
        }

        $this->_dispatch(new ViewEvent($this->viewData, $this->request), $this->config['view_event_name']);
    }

    protected function _dispatchGetResponse($event, $eventName): ?Response
    {
        $this->eventDispatcher->dispatch($event, $eventName);

        if ($event->getResponse()) {
            return $event->getResponse();
        }

        return null;
    }

    protected function _dispatch($event, $eventName): void
    {
        $this->eventDispatcher->dispatch($event, $eventName);
    }
}
