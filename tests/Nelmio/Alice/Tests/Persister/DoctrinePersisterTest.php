<?php

namespace Nelmio\Alice\Tests\Persister;

use Nelmio\Alice\fixtures\User;
use Nelmio\Alice\Persister\DoctrinePersister;

class DoctrinePersisterTest extends \PHPUnit_Framework_TestCase
{
    public function testPersistWithFlush()
    {
        $users = array(
            new User(),
            new User(),
        );

        $om = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ObjectManager');
        $om
            ->expects($this->exactly(2))
            ->method('persist')
        ;
        $om
            ->expects($this->once())
            ->method('flush')
        ;

        $persister = new DoctrinePersister($om, true);
        $persister->persist($users);
    }

    public function testPersistWithoutFlush()
    {
        $user = new User();

        $om = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ObjectManager');
        $om
            ->expects($this->once())
            ->method('persist')
            ->with($user)
        ;
        $om
            ->expects($this->never())
            ->method('flush')
        ;

        $persister = new DoctrinePersister($om, false);
        $persister->persist(array($user));
    }

    public function testFindExistingEntity()
    {
        $user = new User();

        $class = 'Nelmio\Alice\fixtures\User';
        $id = 1;

        $om = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ObjectManager');
        $om
            ->expects($this->once())
            ->method('find')
            ->with($class, $id)
            ->will($this->returnValue($user))
        ;

        $persister = new DoctrinePersister($om);
        $this->assertSame($user, $persister->find($class, $id));
    }

    public function testFindNotExistingEntity()
    {
        $class = 'Nelmio\Alice\fixtures\User';
        $id = 1;

        $om = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ObjectManager');
        $om
            ->expects($this->once())
            ->method('find')
            ->with($class, $id)
            ->will($this->returnValue(null))
        ;

        $this->setExpectedException('\UnexpectedValueException');

        $persister = new DoctrinePersister($om);
        $persister->find($class, $id);
    }
}
