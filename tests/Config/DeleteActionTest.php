<?php

namespace Config;

use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Config\Configuration;

class DeleteActionTest extends TestCase
{
    public function testDeleteEmpty(): void
    {
        $result = Configuration::deleteAction('test');

        $expected = [
            'initialize_event_name' => null,
            'load_entity_event_name' => null,
            'not_found_event_name' => null,
            'found_event_name' => null,
            'param_converter_key' => null,
            'form_prepare_event_name' => null,
            'form_init_event_name' => null,
            'form_valid_event_name' => null,
            'apply_event_name' => null,
            'success_event_name' => null,
            'failure_event_name' => null,
            'form_invalid_event_name' => null,
            'view_event_name' => null,
            'exception_event_name' => null,
            'is_granted' => null,
            'success_redirect_to' => null,
            'entity_attribute' => 'entity',
            'view' => null,
            'form' => null,
        ];

        $this->assertEquals($expected, $result);
    }

    public function testDeleteBasic(): void
    {
        $config = [
            'entity_attribute' => 'test',
            'param_converter_key' => 'id',
            'view' => 'view.html.twig',
            'form' => 'dummy_form_class',
        ];

        $result = Configuration::deleteAction('test', [], $config);

        $expected = $config + [
                'initialize_event_name' => null,
                'load_entity_event_name' => null,
                'not_found_event_name' => null,
                'found_event_name' => null,
                'form_prepare_event_name' => null,
                'form_init_event_name' => null,
                'form_valid_event_name' => null,
                'apply_event_name' => null,
                'success_event_name' => null,
                'failure_event_name' => null,
                'form_invalid_event_name' => null,
                'view_event_name' => null,
                'exception_event_name' => null,
                'is_granted' => null,
                'success_redirect_to' => null,
            ];

        $this->assertEquals($expected, $result);
    }

    public function testDeleteOverride(): void
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
            'failure_event_name' => 'failure_event_name',
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

        $result = Configuration::deleteAction('test', ['test' => $config1], $config2);

        $expected = array_merge($config1, $config2) + [
                'load_entity_event_name' => null,
                'not_found_event_name' => null,
                'found_event_name' => null,
                'apply_event_name' => null,
                'exception_event_name' => null,
            ];

        $this->assertEquals($expected, $result);
    }
}