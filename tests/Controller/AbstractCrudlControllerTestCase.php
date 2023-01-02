<?php

namespace Softspring\Component\CrudlController\Tests\Controller;

use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Controller\CrudlController;
use Softspring\Component\CrudlController\Tests\Manager\ExampleManagerCrudl;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;

abstract class AbstractCrudlControllerTestCase extends TestCase
{
    /**
     * @var MockObject|ExampleManagerCrudl
     */
    protected $manager;

    /**
     * @var MockObject|EntityRepository
     */
    protected $repository;

    /**
     * @var MockObject|EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var MockObject|Container
     */
    protected $container;

    /**
     * @var MockObject|FormFactory
     */
    protected $formFactory;

    protected function setUp(): void
    {
        $this->manager = $this->getMockBuilder(ExampleManagerCrudl::class)->disableOriginalConstructor()->getMock();
        $this->manager->method('createEntity')->willReturn(new \stdClass());

        $this->repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->manager->expects($this->any())->method('getRepository')->willReturn($this->repository);

        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->disableOriginalConstructor()->getMock();
        $this->container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $this->formFactory = $this->getMockBuilder(FormFactory::class)->disableOriginalConstructor()->getMock();

        $test = $this;
        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($service) use ($test) {
                switch ($service) {
                    case 'event_dispatcher':
                        return $test->dispatcher;

                    case 'form.factory':
                        return $test->formFactory;

                    default:
                        return null;
                }
            }))
        ;
    }

    /**
     * @return MockObject|CrudlController
     */
    protected function getControllerMock(array $config, array $onlyMethods = [])
    {
        $controller = $this->getMockBuilder(CrudlController::class)
            ->setConstructorArgs([$this->manager, $this->dispatcher, $config])
            ->onlyMethods($onlyMethods)
            ->getMock();

        $controller->setContainer($this->container);

        return $controller;
    }
}
