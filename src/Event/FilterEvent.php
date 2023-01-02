<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class FilterEvent extends Event
{
    protected array $filters;

    protected array $orderSort;

    protected ?int $page;

    protected ?int $rpp;

    public static function createFromFilterForm(FormInterface $form, Request $request): FilterEvent
    {
        $formCompiledOptions = $form->getConfig()->getOptions();

        $page = $request->get($formCompiledOptions['page_field_name'], 1);
        $rpp = $form->get($formCompiledOptions['rpp_field_name'])->getData() ?? $formCompiledOptions['rpp_default_value'];
        $orderSort = [$form->get($formCompiledOptions['order_field_name'])->getData() ?? $formCompiledOptions['order_default_value'] => $form->get($formCompiledOptions['order_direction_field_name'])->getData() ?? $formCompiledOptions['order_direction_default_value']];

        $filters = $form->isSubmitted() && $form->isValid() ? array_filter($form->getData()) : [];

        return new FilterEvent($filters, $orderSort, $page, $rpp);
    }

    public function __construct(array $filters, array $orderSort, ?int $page = null, ?int $rpp = null)
    {
        $this->filters = $filters;
        $this->orderSort = $orderSort;
        $this->page = $page;
        $this->rpp = $rpp;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }

    public function getOrderSort(): array
    {
        return $this->orderSort;
    }

    public function setOrderSort(array $orderSort): void
    {
        $this->orderSort = $orderSort;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getRpp(): ?int
    {
        return $this->rpp;
    }

    public function setRpp(?int $rpp): void
    {
        $this->rpp = $rpp;
    }
}
