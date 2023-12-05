<?php

namespace Softspring\Component\CrudlController\Tests\Config;

use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Config\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class CreateActionTest extends TestCase
{
    public function testCreateEmpty()
    {
        $this->expectException(InvalidConfigurationException::class);
        Configuration::createAction('test');
    }

    public function testCreateBasic()
    {
        $config = [
            'entity_attribute' => 'test',
            'view' => 'view.html.twig',
            'form' => 'dummy_form_class',
        ];

        $result = Configuration::createAction('test', [], $config);

        $this->assertEquals($config, $result);
    }

    public function testCreateOverride()
    {
        $config1 = [
            'entity_attribute' => 'test',
            'view' => 'view.html.twig',
            'form' => 'dummy_form_class',
            'initialize_event_name' => 'initialize_event_name',
            'form_prepare_event_name' => 'form_prepare_event_name',
            'form_init_event_name' => 'form_init_event_name',
            'form_valid_event_name' => 'form_valid_event_name',
            'success_event_name' => 'success_event_name',
            'exception_event_name' => 'exception_event_name',
            'form_invalid_event_name' => 'form_invalid_event_name',
            'view_event_name' => 'view_event_name',
        ];

        $config2 = [
            'entity_attribute' => 'test2',
            'form' => 'dummy_form_class2',
            'is_granted' => 'is_granted2',
            'view' => 'view2',
            'success_redirect_to' => 'success_redirect_to2',
        ];

        $result = Configuration::createAction('test', [ 'test' => $config1], $config2);

        $this->assertEquals(array_merge($config1, $config2), $result);
    }
}