<?php

namespace Softspring\Component\CrudlController\Event;

use Doctrine\ORM\QueryBuilder;
use Softspring\Component\DoctrinePaginator\Collection\PaginatedCollection;
use Softspring\Component\DoctrinePaginator\Paginator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class FilterEvent extends Event
{
    protected array $filters;

    protected array $orderSort;

    protected ?int $page;

    protected ?int $rpp;

    protected ?QueryBuilder $queryBuilder;

    protected ?int $filtersMode;

    public static function createFromFilterForm(FormInterface $form, Request $request): FilterEvent
    {
        [$qb, $page, $rpp, $filters, $orderSort, $filtersMode] = Paginator::processPaginatedFilterForm($form, $request);

        return new FilterEvent($filters, $orderSort, $page, $rpp, $qb, $filtersMode);
    }

    public function __construct(array $filters, array $orderSort, ?int $page = null, ?int $rpp = null, ?QueryBuilder $queryBuilder = null, ?int $filtersMode = null)
    {
        $this->filters = $filters;
        $this->orderSort = $orderSort;
        $this->page = $page;
        $this->rpp = $rpp;
        $this->queryBuilder = $queryBuilder;
        $this->filtersMode = $filtersMode;
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

    public function getQueryBuilder(): ?QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder(?QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function getFiltersMode(): ?int
    {
        return $this->filtersMode;
    }

    public function setFiltersMode(?int $filtersMode): void
    {
        $this->filtersMode = $filtersMode;
    }

    public function queryPage(): PaginatedCollection
    {
        return Paginator::queryPage($this->getQueryBuilder(), $this->getPage(), $this->getRpp(), $this->getFilters(), $this->getOrderSort(), $this->getFiltersMode());
    }
}
