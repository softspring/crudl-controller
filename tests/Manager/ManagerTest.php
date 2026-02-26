<?php

namespace Softspring\Component\CrudlController\Tests\Manager;

use ReflectionClass;
use InvalidArgumentException;
use stdClass;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testGetTargetClass(): void
    {
        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();

        $manager = new ExampleManagerCrudl($em);
        $this->assertEquals('Softspring\\Component\\CrudlController\\Tests\\Manager\\ExampleEntity', $manager->getTargetClass());
    }

    public function testGetEntityClass(): void
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $metadata->expects($this->once())
            ->method('getReflectionClass')
            ->willReturn(new ReflectionClass(ExampleEntity::class))
        ;

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo('Softspring\\Component\\CrudlController\\Tests\\Manager\\ExampleEntity'))
            ->willReturn($metadata)
        ;

        $manager = new ExampleManagerCrudl($em);
        $this->assertEquals('Softspring\\Component\\CrudlController\\Tests\\Manager\\ExampleEntity', $manager->getEntityClass());
    }

    public function testCreateEntity(): void
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $metadata->expects($this->once())
            ->method('getReflectionClass')
            ->willReturn(new ReflectionClass(ExampleEntity::class))
        ;

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo('Softspring\\Component\\CrudlController\\Tests\\Manager\\ExampleEntity'))
            ->willReturn($metadata)
        ;

        $manager = new ExampleManagerCrudl($em);
        $this->assertInstanceOf(ExampleEntity::class, $manager->createEntity());
    }

    public function testGetRepository(): void
    {
        $repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('Softspring\\Component\\CrudlController\\Tests\\Manager\\ExampleEntity'))
            ->willReturn($repository)
        ;

        $manager = new ExampleManagerCrudl($em);
        $this->assertEquals($repository, $manager->getRepository());
    }

    public function testSaveEntity(): void
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $metadata->expects($this->once())
            ->method('getReflectionClass')
            ->willReturn(new ReflectionClass(ExampleEntity::class))
        ;

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo('Softspring\\Component\\CrudlController\\Tests\\Manager\\ExampleEntity'))
            ->willReturn($metadata)
        ;
        $em->expects($this->once())
            ->method('persist')
        ;

        $manager = new ExampleManagerCrudl($em);
        $manager->saveEntity(new ExampleEntity());
    }

    public function testInvalidSaveEntity(): void
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $metadata->expects($this->any())
            ->method('getReflectionClass')
            ->willReturn(new ReflectionClass(ExampleEntity::class))
        ;

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo('Softspring\\Component\\CrudlController\\Tests\\Manager\\ExampleEntity'))
            ->willReturn($metadata)
        ;

        $this->expectException(InvalidArgumentException::class);

        $manager = new ExampleManagerCrudl($em);
        $manager->saveEntity(new stdClass());
    }
}
