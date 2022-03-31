<?php

namespace Softspring\Component\CrudlController\Tests\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class ManagerTest extends TestCase
{
    public function testGetTargetClass()
    {
        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();

        $manager = new ExampleManagerCrudl($em);
        $this->assertEquals('Softspring\\CrudlBundle\\Tests\\Manager\\ExampleEntity', $manager->getTargetClass());
    }

    public function testGetEntityClass()
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $metadata->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue(new \ReflectionClass(ExampleEntity::class)))
        ;

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo('Softspring\\CrudlBundle\\Tests\\Manager\\ExampleEntity'))
            ->will($this->returnValue($metadata))
        ;

        $manager = new ExampleManagerCrudl($em);
        $this->assertEquals('Softspring\\CrudlBundle\\Tests\\Manager\\ExampleEntity', $manager->getEntityClass());
    }

    public function testCreateEntity()
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $metadata->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue(new \ReflectionClass(ExampleEntity::class)))
        ;

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo('Softspring\\CrudlBundle\\Tests\\Manager\\ExampleEntity'))
            ->will($this->returnValue($metadata))
        ;

        $manager = new ExampleManagerCrudl($em);
        $this->assertInstanceOf(ExampleEntity::class, $manager->createEntity());
    }

    public function testGetRepository()
    {
        $repository = $this->getMockBuilder(EntityRepository::class)->disableOriginalConstructor()->getMock();

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('Softspring\\CrudlBundle\\Tests\\Manager\\ExampleEntity'))
            ->will($this->returnValue($repository))
        ;

        $manager = new ExampleManagerCrudl($em);
        $this->assertEquals($repository, $manager->getRepository());
    }

    public function testSaveEntity()
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $metadata->expects($this->once())
            ->method('getReflectionClass')
            ->will($this->returnValue(new \ReflectionClass(ExampleEntity::class)))
        ;

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->equalTo('Softspring\\CrudlBundle\\Tests\\Manager\\ExampleEntity'))
            ->will($this->returnValue($metadata))
        ;
        $em->expects($this->once())
            ->method('persist')
        ;

        $manager = new ExampleManagerCrudl($em);
        $manager->saveEntity(new ExampleEntity());
    }

    public function testInvalidSaveEntity()
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)->disableOriginalConstructor()->getMock();
        $metadata->expects($this->any())
            ->method('getReflectionClass')
            ->will($this->returnValue(new \ReflectionClass(ExampleEntity::class)))
        ;

        $em = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $em->expects($this->any())
            ->method('getClassMetadata')
            ->with($this->equalTo('Softspring\\CrudlBundle\\Tests\\Manager\\ExampleEntity'))
            ->will($this->returnValue($metadata))
        ;

        $this->expectException(\InvalidArgumentException::class);

        $manager = new ExampleManagerCrudl($em);
        $manager->saveEntity(new \stdClass());
    }
}
