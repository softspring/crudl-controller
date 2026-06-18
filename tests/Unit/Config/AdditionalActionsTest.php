<?php

declare(strict_types=1);

namespace Softspring\Component\CrudlController\Tests\Unit\Config;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Config\Configuration;
use Softspring\Component\CrudlController\Config\ListActionConfiguration;
use Softspring\Component\CrudlController\Config\ReadActionConfiguration;
use Softspring\Component\CrudlController\Config\TransitionActionConfiguration;
use Symfony\Component\Config\Definition\Processor;

final class AdditionalActionsTest extends TestCase
{
    public function testListActionMergesControllerAndActionConfiguration(): void
    {
        $result = Configuration::listAction('products', [
            'products' => [
                'entity_attribute' => 'product',
                'entities_attribute' => 'products',
                'view_data' => ['section' => 'catalog'],
            ],
        ], [
            'view' => 'admin/products/list.html.twig',
            'read_route' => 'admin_product_read',
        ]);

        self::assertSame('list', $result['action']);
        self::assertSame('product', $result['entity_attribute']);
        self::assertSame('products', $result['entities_attribute']);
        self::assertSame('admin/products/list.html.twig', $result['view']);
        self::assertSame('admin_product_read', $result['read_route']);
        self::assertSame(['section' => 'catalog'], $result['view_data']);
    }

    public function testReadActionDropsEmptyViewData(): void
    {
        $result = Configuration::readAction('products', [
            'products' => [
                'view_data' => [],
            ],
        ]);

        self::assertSame('read', $result['action']);
        self::assertSame([], $result['view_data']);
    }

    public function testActionActionUsesApplyConfiguration(): void
    {
        $result = Configuration::actionAction('products', [], [
            'entity_attribute' => 'product',
            'param_converter_key' => 'product',
            'success_redirect_to' => 'admin_product_list',
        ]);

        self::assertSame('apply', $result['action']);
        self::assertSame('product', $result['entity_attribute']);
        self::assertSame('product', $result['param_converter_key']);
        self::assertSame('admin_product_list', $result['success_redirect_to']);
    }

    public function testTransitionActionKeepsWorkflowOptions(): void
    {
        $result = Configuration::transitionAction('orders', [], [
            'transition_attribute' => 'transition',
            'workflow_name' => 'order_flow',
            'form' => 'app.transition_form',
        ]);

        self::assertSame('transition', $result['action']);
        self::assertSame('transition', $result['transition_attribute']);
        self::assertSame('order_flow', $result['workflow_name']);
        self::assertSame('app.transition_form', $result['form']);
    }

    public function testActionConfigurationsRejectWrongActionNames(): void
    {
        $processor = new Processor();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('List action configuration must have action list');

        $processor->processConfiguration(new ListActionConfiguration(), [[
            'action' => 'read',
        ]]);
    }

    public function testReadConfigurationRejectsWrongActionName(): void
    {
        $processor = new Processor();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Read action configuration must have action read');

        $processor->processConfiguration(new ReadActionConfiguration(), [[
            'action' => 'list',
        ]]);
    }

    public function testTransitionConfigurationRejectsWrongActionName(): void
    {
        $processor = new Processor();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Transition action configuration must have action transition');

        $processor->processConfiguration(new TransitionActionConfiguration(), [[
            'action' => 'update',
        ]]);
    }
}
