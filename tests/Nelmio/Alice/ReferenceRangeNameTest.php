<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\support\models\Group;
use Nelmio\Alice\support\models\Task;
use Nelmio\Alice\support\models\User;
use PHPUnit\Framework\TestCase;

class ReferenceRangeNameTest extends TestCase
{
    /**
     * @expectedException \UnexpectedValueException
     */
    public function testThrowExceptionWhenReferencesAreNotFound()
    {
        $managerMock = $this->getDoctrineManagerMock();

        $files = [
            __DIR__ . '/support/fixtures/reference_range_2.yml',
        ];

        Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testThrowExceptionWhenSelfReferencesAreNotFound()
    {
        $managerMock = $this->getDoctrineManagerMock();

        $files = [
            __DIR__ . '/support/fixtures/reference_range_with_self_reference.yml',
        ];

        Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);
    }

    public function testLoadFixturesByReference()
    {
        $managerMock = $this->getDoctrineManagerMock();

        $files = [
            __DIR__ . '/support/fixtures/reference_range_1.yml',
            __DIR__ . '/support/fixtures/reference_range_2.yml',
            __DIR__ . '/support/fixtures/reference_range_3.yml',
        ];
        $objects = Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);

        $this->assertCount(11, $objects);

        $groupReference = $objects['group_1_user1'];
        $this->assertInstanceOf(Group::class, $groupReference);
        $this->assertInstanceOf(User::class, $groupReference->getOwner());
        $this->assertEquals($objects['user1'], $groupReference->getOwner());
        $this->assertCount(3, $groupReference->getMembers());

        $groupReferenceList1 = $objects['group_list_user1'];
        $this->assertInstanceOf(Group::class, $groupReferenceList1);
        $this->assertInstanceOf(User::class, $groupReferenceList1->getOwner());
        $this->assertEquals($objects['user1'], $groupReferenceList1->getOwner());

        $groupReferenceList2 = $objects['group_list_user2'];
        $this->assertInstanceOf(Group::class, $groupReferenceList2);
        $this->assertInstanceOf(User::class, $groupReferenceList2->getOwner());
        $this->assertEquals($objects['user2'], $groupReferenceList2->getOwner());

        $groupReferenceList3 = $objects['group_list_user3'];
        $this->assertInstanceOf(Group::class, $groupReferenceList3);
        $this->assertInstanceOf(User::class, $groupReferenceList3->getOwner());
        $this->assertEquals($objects['user3'], $groupReferenceList3->getOwner());

        $taskGroupUser1 = $objects['task_1_group_1_user1'];
        $this->assertInstanceOf(Task::class, $taskGroupUser1);

        $taskList1 = $objects['task_list_group_list_user1'];
        $this->assertInstanceOf(Task::class, $taskList1);
    }

    public function testLoadFixturesByReferenceWithRangeList()
    {
        $managerMock = $this->getDoctrineManagerMock();

        $files = [
            __DIR__ . '/support/fixtures/reference_range_with_range_list_1.yml',
            __DIR__ . '/support/fixtures/reference_range_with_range_list_2.yml',
        ];
        $objects = Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);

        $this->assertCount(5, $objects);

        $groupReferenceUserAlice = $objects['group_user_alice'];
        $this->assertInstanceOf(Group::class, $groupReferenceUserAlice);
        $this->assertInstanceOf(User::class, $groupReferenceUserAlice->getOwner());
        $this->assertEquals($objects['user_alice'], $groupReferenceUserAlice->getOwner());

        $groupReferenceUserBob = $objects['group_user_bob'];
        $this->assertInstanceOf(Group::class, $groupReferenceUserBob);
        $this->assertInstanceOf(User::class, $groupReferenceUserBob->getOwner());
        $this->assertEquals($objects['user_bob'], $groupReferenceUserBob->getOwner());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDoctrineManagerMock()
    {
        $managerMock = $this->createMock(ObjectManager::class);
        $metadataFactoryMock = $this->createMock(ClassMetadataFactory::class);
        $metadataMock = $this->createMock(ClassMetadata::class);

        $managerMock
            ->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactoryMock))
        ;

        $metadataFactoryMock
            ->method('getAllMetadata')
            ->will($this->returnValue([$metadataMock]))
        ;

        $managerMock->method('flush');
        $managerMock->method('persist');

        return $managerMock;
    }
}
