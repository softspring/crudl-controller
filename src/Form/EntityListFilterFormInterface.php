<?php

namespace Softspring\Component\CrudlController\Form;

use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated this interface will be deleted
 */
interface EntityListFilterFormInterface extends FormTypeInterface
{
    /**
     * @deprecated use new softspring/doctrine-query-filters FiltersForm
     */
    public static function getPage(Request $request): int;

    /**
     * @deprecated use new softspring/doctrine-query-filters FiltersForm
     */
    public static function getRpp(Request $request): int;

    /**
     * @deprecated use new softspring/doctrine-query-filters FiltersForm
     */
    public static function getOrder(Request $request): array;

    /**
     * @deprecated use new softspring/doctrine-query-filters FiltersForm
     */
    public static function pageParamName(): string;

    /**
     * @deprecated use new softspring/doctrine-query-filters FiltersForm
     */
    public static function rppParamName(): string;

    /**
     * @deprecated use new softspring/doctrine-query-filters FiltersForm
     */
    public static function orderFieldParamName(): string;

    /**
     * @deprecated use new softspring/doctrine-query-filters FiltersForm
     */
    public static function orderDirectionParamName(): string;
}
