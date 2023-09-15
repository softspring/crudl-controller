<?php

namespace Softspring\Component\CrudlController\Form;

use Jhg\DoctrinePaginationBundle\Request\RequestParam;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated this class will be deleted, use Softspring\Component\DoctrineQueryFilters\FiltersForm
 */
class EntityListFilterForm extends AbstractType implements EntityListFilterFormInterface
{
    public function getBlockPrefix(): string
    {
        return '';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'GET',
            'required' => false,
            'attr' => ['novalidate' => 'novalidate'],
            'allow_extra_fields' => true,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(static::orderFieldParamName(), HiddenType::class, [
            'mapped' => false,
        ]);

        $builder->add(static::orderDirectionParamName(), HiddenType::class, [
            'mapped' => false,
        ]);

        $builder->add(static::rppParamName(), HiddenType::class, [
            'mapped' => false,
        ]);
    }

    public static function getPage(Request $request): int
    {
        return (int) ($request->query->get(static::pageParamName()) ?: 1);
    }

    public static function getRpp(Request $request): int
    {
        return (int) ($request->query->get(static::rppParamName()) ?: static::rppDefault());
    }

    public static function getOrder(Request $request): array
    {
        if (class_exists(RequestParam::class)) {
            $order = RequestParam::getQueryValidParam($request, static::orderFieldParamName(), static::orderDefaultField(), static::orderValidFields());
            $sort = RequestParam::getQueryValidParam($request, static::orderDirectionParamName(), 'asc', ['asc', 'desc']);

            return [$order => $sort];
        }

        return [$request->query->get(static::orderFieldParamName(), '') ?: 'id' => $request->query->get(static::orderDirectionParamName(), '') ?: 'asc'];
    }

    public static function pageParamName(): string
    {
        return 'page';
    }

    public static function rppParamName(): string
    {
        return 'rpp';
    }

    public static function rppDefault(): int
    {
        return 50;
    }

    public static function orderFieldParamName(): string
    {
        return 'sort';
    }

    public static function orderDirectionParamName(): string
    {
        return 'order';
    }

    public static function orderValidFields(): array
    {
        return ['id'];
    }

    public static function orderDefaultField(): string
    {
        return 'id';
    }
}
