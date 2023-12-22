<?php

namespace Softspring\Component\CrudlController\Tests\Event;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Event\FilterEvent;
use Symfony\Component\HttpFoundation\Request;

class FilterEventTest extends TestCase
{
    public function testDefault(): void
    {
        $request = new Request();
        $event = new FilterEvent($request, [], []);

        $this->assertEquals($request, $event->getRequest());
        $this->assertEquals([], $event->getFilters());
        $this->assertEquals([], $event->getOrderSort());
        $this->assertNull($event->getPage());
        $this->assertNull($event->getRpp());
        $this->assertNull($event->getQueryBuilder());
        $this->assertNull($event->getFiltersMode());
    }

    public function testFilters(): void
    {
        $request = new Request();
        $event = new FilterEvent($request, ['foo' => 'bar'], []);
        $this->assertEquals(['foo' => 'bar'], $event->getFilters());
        $event->setFilters(['foo' => 'bar', 'bar' => 'foo']);
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $event->getFilters());
    }

    public function testOrderSort(): void
    {
        $request = new Request();
        $event = new FilterEvent($request, [], ['foo' => 'asc']);
        $this->assertEquals(['foo' => 'asc'], $event->getOrderSort());
        $event->setOrderSort(['foo' => 'asc', 'bar' => 'desc']);
        $this->assertEquals(['foo' => 'asc', 'bar' => 'desc'], $event->getOrderSort());
    }

    public function testPage(): void
    {
        $request = new Request();
        $event = new FilterEvent($request, [], [], 1);
        $this->assertEquals(1, $event->getPage());
        $event->setPage(2);
        $this->assertEquals(2, $event->getPage());
    }

    public function testRpp(): void
    {
        $request = new Request();
        $event = new FilterEvent($request, [], [], null, 10);
        $this->assertEquals(10, $event->getRpp());
        $event->setRpp(20);
        $this->assertEquals(20, $event->getRpp());
    }

    public function testQueryBuilder(): void
    {
        $request = new Request();
        $qb1 = $this->createMock(QueryBuilder::class);
        $event = new FilterEvent($request, [], [], null, null, $qb1);
        $this->assertEquals($qb1, $event->getQueryBuilder());

        $qb2 = $this->createMock(QueryBuilder::class);
        $event->setQueryBuilder($qb2);
        $this->assertEquals($qb2, $event->getQueryBuilder());
    }

    public function testFiltersMode(): void
    {
        $request = new Request();
        $event = new FilterEvent($request, [], [], null, null, null, 1);
        $this->assertEquals(1, $event->getFiltersMode());
        $event->setFiltersMode(2);
        $this->assertEquals(2, $event->getFiltersMode());
    }
}