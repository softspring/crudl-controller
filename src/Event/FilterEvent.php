<?php

namespace Softspring\Component\CrudlController\Event;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Softspring\Component\DoctrinePaginator\Collection\PaginatedCollection;
use Softspring\Component\DoctrinePaginator\Exception\InvalidFormTypeException;
use Softspring\Component\DoctrinePaginator\Paginator;
use Softspring\Component\DoctrineQueryFilters\Exception\InvalidFilterValueException;
use Softspring\Component\DoctrineQueryFilters\Exception\MissingFromInQueryBuilderException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\Event;

class FilterEvent extends Event
{
    /**
     * @throws InvalidFormTypeException
     */
    public static function createFromFilterForm(FormInterface $form, Request $request): FilterEvent
    {
        [$qb, $page, $rpp, $filters, $orderSort, $filtersMode] = Paginator::processPaginatedFilterForm($form, $request);

        return new FilterEvent($request, $filters, $orderSort, $page, $rpp, $qb, $filtersMode);
    }

    public function __construct(
        protected Request $request,
        protected array $filters,
        protected array $orderSort,
        protected ?int $page = null,
        protected ?int $rpp = null,
        protected ?QueryBuilder $queryBuilder = null,
        protected ?int $filtersMode = null
    ) {
    }

    public function getRequest(): Request
    {
        return $this->request;
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

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): void
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

    /**
     * @throws InvalidFilterValueException
     * @throws MissingFromInQueryBuilderException
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function queryPage(): PaginatedCollection
    {
        return Paginator::queryPage($this->getQueryBuilder(), $this->getPage(), $this->getRpp(), $this->getFilters(), $this->getOrderSort(), $this->getFiltersMode());
    }
}
