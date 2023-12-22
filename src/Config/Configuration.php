<?php

namespace Softspring\Component\CrudlController\Config;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

class Configuration
{
    public static function createAction(string $controllerConfigsKey, array $controllerConfigs = [], array $actionConfig = []): array
    {
        return self::processAction(new CreateActionConfiguration(), 'create', $controllerConfigsKey, $controllerConfigs, $actionConfig);
    }

    public static function readAction(string $controllerConfigsKey, array $controllerConfigs = [], array $actionConfig = []): array
    {
        return self::processAction(new ReadActionConfiguration(), 'read', $controllerConfigsKey, $controllerConfigs, $actionConfig);
    }

    public static function updateAction(string $controllerConfigsKey, array $controllerConfigs = [], array $actionConfig = []): array
    {
        return self::processAction(new UpdateActionConfiguration(), 'update', $controllerConfigsKey, $controllerConfigs, $actionConfig);
    }

    public static function deleteAction(string $controllerConfigsKey, array $controllerConfigs = [], array $actionConfig = []): array
    {
        return self::processAction(new DeleteActionConfiguration(), 'delete', $controllerConfigsKey, $controllerConfigs, $actionConfig);
    }

    public static function listAction(string $controllerConfigsKey, array $controllerConfigs = [], array $actionConfig = []): array
    {
        return self::processAction(new ListActionConfiguration(), 'list', $controllerConfigsKey, $controllerConfigs, $actionConfig);
    }

    public static function actionAction(string $controllerConfigsKey, array $controllerConfigs = [], array $actionConfig = []): array
    {
        return self::processAction(new ApplyActionConfiguration(), 'action', $controllerConfigsKey, $controllerConfigs, $actionConfig);
    }

    protected static function processAction(ConfigurationInterface $configuration, string $action, string $controllerConfigsKey, array $controllerConfigs = [], array $actionConfig = []): array
    {
        $processor = new Processor();

        $mergedConfigs = array_merge($controllerConfigs[$controllerConfigsKey] ?? [], $actionConfig);

        return $processor->processConfiguration($configuration, [$mergedConfigs]);
    }
}
