<?php

namespace Softspring\Component\CrudlController\Form;

use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;

interface EntityListFilterFormInterface extends FormTypeInterface
{
    public static function getPage(Request $request): int;

    public static function getRpp(Request $request): int;

    public static function getOrder(Request $request): array;

    public static function pageParamName(): string;

    public static function rppParamName(): string;

    public static function orderFieldParamName(): string;

    public static function orderDirectionParamName(): string;
}
