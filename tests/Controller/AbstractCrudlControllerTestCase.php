<?php

namespace Softspring\Component\CrudlController\Tests\Controller;

use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Controller\CrudlController;
use Softspring\Component\CrudlController\Tests\Manager\ExampleManagerCrudl;
use stdClass;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

abstract class AbstractCrudlControllerTestCase extends TestCase
{
    /** @var ExampleManagerCrudl&MockObject */
    protected MockObject $manager;
    /** @var EntityRepository&MockObject */
    protected MockObject $repository;
    /** @var EventDispatcherInterface&MockObject */
    protected MockObject $dispatcher;
    /** @var Container&MockObject */
    protected MockObject $container;
    /** @var FormFactory&MockObject */
    protected MockObject $formFactory;
    /** @var Environment&MockObject */
    protected MockObject $twig;
    /** @var AuthorizationCheckerInterface&MockObject */
    protected MockObject $authorizationChecker;
    /** @var RouterInterface&MockObject */
    protected MockObject $router;

    protected function setUp(): void
    {
        $this->manager = $this->getMockBuilder(ExampleManagerCrudl::class)->disableOriginalConstructor()->getMock();
        $this->manager->method('createEntity')->willReturn(new stdClass());

        $this->repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->manager->expects($this->any())->method('getRepository')->willReturn($this->repository);

        $this->dispatcher = $this->getMockBuilder(EventDispatcherInterface::class)->disableOriginalConstructor()->getMock();
        $this->container = $this->getMockBuilder(Container::class)->disableOriginalConstructor()->getMock();
        $this->formFactory = $this->getMockBuilder(FormFactory::class)->disableOriginalConstructor()->getMock();
        $this->twig = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $this->authorizationChecker = $this->getMockBuilder(AuthorizationCheckerInterface::class)->disableOriginalConstructor()->getMock();
        $this->router = $this->getMockBuilder(RouterInterface::class)->disableOriginalConstructor()->getMock();
    }

    protected function createControllerMock(array $configs, array $onlyMethods = []): MockObject|CrudlController
    {
        return $this->getMockBuilder(CrudlController::class)
            ->setConstructorArgs([
                $this->manager,
                $this->dispatcher,
                $this->twig,
                $this->formFactory,
                $this->authorizationChecker,
                $this->router,
                $configs])
            ->onlyMethods($onlyMethods)
            ->getMock();
    }

    protected function createController(array $configs): CrudlController
    {
        return new CrudlController($this->manager, $this->dispatcher, $this->twig, $this->formFactory, $this->authorizationChecker, $this->router, $configs);
    }
}
