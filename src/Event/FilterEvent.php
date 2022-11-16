<?php

namespace Softspring\Component\CrudlController\Event;

use Symfony\Contracts\EventDispatcher\Event;

class FilterEvent extends Event
{
    protected array $filters;

    protected array $orderSort;

    protected ?int $page;

    protected ?int $rpp;

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
