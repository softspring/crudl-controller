<?php

namespace Softspring\Component\CrudlController\Controller;

use Softspring\Component\Events\GetResponseEventInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\EventDispatcher\Event;

trait DispatchFromConfigTrait
{
    protected function dispatchGetResponseFromConfig(array $config, string $eventNameKey, GetResponseEventInterface $event): ?Response
    {
        if (isset($config[$eventNameKey])) {
            if ($response = $this->dispatchGetResponse($config[$eventNameKey], $event)) {
                return $response;
            }
        }

        return null;
    }

    protected function dispatchFromConfig(array $config, string $eventNameKey, Event $event): void
    {
        if (isset($config[$eventNameKey])) {
            $this->dispatch($config[$eventNameKey], $event);
        }
    }
}
