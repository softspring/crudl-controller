<?php

namespace Config;

use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Config\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class DeleteActionTest extends TestCase
{
    public function testDeleteEmpty()
    {
        $this->expectException(InvalidConfigurationException::class);
        Configuration::deleteAction('test');
    }

    public function testDeleteBasic()
    {
        $config = [
            'entity_attribute' => 'test',
            'param_converter_key' => 'id',
            'view' => 'view.html.twig',
            'form' => 'dummy_form_class',
        ];

        $result = Configuration::deleteAction('test', [], $config);

        $this->assertEquals($config, $result);
    }

    public function testDeleteOverride()
    {
        $config1 = [
            'entity_attribute' => 'test',
            'param_converter_key' => 'id',
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
            'param_converter_key' => 'param_converter_key2',
            'is_granted' => 'is_granted2',
            'view' => 'view2',
            'success_redirect_to' => 'success_redirect_to2',
        ];

        $result = Configuration::deleteAction('test', [ 'test' => $config1], $config2);

        $this->assertEquals(array_merge($config1, $config2), $result);
    }
}