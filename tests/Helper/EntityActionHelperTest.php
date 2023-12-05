<?php

namespace Helper;

use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Softspring\Component\CrudlController\Event\GetResponseEntityEvent;
use Softspring\Component\CrudlController\Helper\EntityActionHelper;
use Softspring\Component\CrudlController\Manager\CrudlEntityManagerInterface;
use Softspring\Component\Events\GetResponseRequestEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Twig\Environment;

class EntityActionHelperTest extends TestCase
{
    protected MockObject|CrudlEntityManagerInterface $managerMock;
    protected MockObject|EventDispatcherInterface $eventDispatcherMock;
    protected MockObject|Environment $twigMock;
    protected MockObject|AuthorizationCheckerInterface $authorizationCheckerMock;
    protected MockObject|RouterInterface $routerMock;

    protected function setUp(): void
    {
        $this->managerMock = $this->createMock(CrudlEntityManagerInterface::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $this->twigMock = $this->createMock(Environment::class);
        $this->authorizationCheckerMock = $this->createMock(AuthorizationCheckerInterface::class);
        $this->routerMock = $this->createMock(RouterInterface::class);
    }

    public function testGetEntity(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);

        $this->assertNull($helper->getEntity());
        $this->assertTrue($helper->notFound());
    }

    public function testCreateEntity(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);

        $entity = $helper->createEntity();
        $this->assertEquals($entity, $helper->getEntity());
        $this->assertFalse($helper->notFound());
    }

    public function testFindEntity(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);
        $helper->setConfig([
            'param_converter_key' => 'id',
            'entity_attribute' => 'entity',
        ]);
        $helper->setRequest(new Request([], [], ['entity' => '123']));

        $repositoryMock = $this->createMock(EntityRepository::class);
        $this->managerMock->expects($this->once())->method('getRepository')->willReturn($repositoryMock);
        $repositoryMock->expects($this->once())->method('findOneBy')->with(['id' => '123'])->willReturn($entity = new \stdClass());

        $helper->findEntity();

        $this->assertEquals($entity, $helper->getEntity());
        $this->assertFalse($helper->notFound());
    }

    public function testCheckIsGranted(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);
        $helper->setConfig([
            'is_granted' => 'ROLE_TEST',
        ]);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied, user is not ROLE_TEST.');

        $helper->checkIsGranted();
    }

    public function testCreateViewData(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);
        $helper->setConfig([
            'entity_attribute' => 'entity',
        ]);

        $entity = $helper->createEntity();

        $viewData = $helper->createViewData();
        $this->assertInstanceOf(\ArrayObject::class, $viewData);
        $this->assertArrayHasKey('entity', $viewData->getArrayCopy());
        $this->assertEquals($entity, $viewData['entity']);
    }

    public function testDispatchInitializeEventNotConfigured(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);
        $helper->setRequest(new Request());
        $helper->setConfig([
            'initialize_event_name' => null,
        ]);
        $this->eventDispatcherMock->expects($this->never())->method('dispatch');
        $helper->dispatchInitializeEvent();
    }

    public function testDispatchInitializeEventNoResponse(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);
        $helper->setRequest(new Request());
        $helper->setConfig([
            'initialize_event_name' => 'test',
        ]);
        $helper->createEntity();

        $this->eventDispatcherMock->expects($this->once())->method('dispatch');
        $response = $helper->dispatchInitializeEvent();
        $this->assertNull($response);
    }

    public function testDispatchInitializeEventWithResponse(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);
        $helper->setRequest(new Request());
        $helper->setConfig([
            'initialize_event_name' => 'test',
        ]);
        $helper->createEntity();

        $expectedResponse = new Response();
        $this->eventDispatcherMock->expects($this->once())->method('dispatch')->willReturnCallback(function (GetResponseEntityEvent $event) use ($expectedResponse) {
            $event->setResponse($expectedResponse);

            return $event;
        });
        $response = $helper->dispatchInitializeEvent();
        $this->assertEquals($expectedResponse, $response);
    }

    public function testDispatchNotFoundEventNotConfigured(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);
        $helper->setRequest(new Request());
        $helper->setConfig([
            'not_found_event_name' => null,
        ]);
        $this->eventDispatcherMock->expects($this->never())->method('dispatch');
        $helper->dispatchNotFoundEvent();
    }

    public function testDispatchNotFoundEventNoResponse(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);
        $helper->setRequest(new Request());
        $helper->setConfig([
            'not_found_event_name' => 'test',
        ]);
        $helper->createEntity();

        $this->eventDispatcherMock->expects($this->once())->method('dispatch');
        $response = $helper->dispatchNotFoundEvent();
        $this->assertNull($response);
    }

    public function testDispatchNotFoundEventWithResponse(): void
    {
        $helper = new EntityActionHelper($this->managerMock, $this->eventDispatcherMock, $this->twigMock, $this->authorizationCheckerMock, $this->routerMock);
        $helper->setRequest(new Request());
        $helper->setConfig([
            'not_found_event_name' => 'test',
        ]);
        $helper->createEntity();

        $expectedResponse = new Response();
        $this->eventDispatcherMock->expects($this->once())->method('dispatch')->willReturnCallback(function (GetResponseRequestEvent $event) use ($expectedResponse) {
            $event->setResponse($expectedResponse);

            return $event;
        });
        $response = $helper->dispatchNotFoundEvent();
        $this->assertEquals($expectedResponse, $response);
    }

}