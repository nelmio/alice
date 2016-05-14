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

class ReferenceRangeNameTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\support\models\User';
    const GROUP = 'Nelmio\Alice\support\models\Group';
    const TASK = 'Nelmio\Alice\support\models\Task';

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testThrowExceptionWhenReferencesAreNotFound()
    {
        $managerMock = $this->getDoctrineManagerMock(null);

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
        $managerMock = $this->getDoctrineManagerMock(null);

        $files = [
            __DIR__ . '/support/fixtures/reference_range_with_self_reference.yml',
        ];

        Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);
    }

    /**
     *
     */
    public function testLoadFixturesByReference()
    {
        $managerMock = $this->getDoctrineManagerMock(11);

        $managerMock->expects($this->exactly(3))
            ->method('flush');

        $files = [
            __DIR__ . '/support/fixtures/reference_range_1.yml',
            __DIR__ . '/support/fixtures/reference_range_2.yml',
            __DIR__ . '/support/fixtures/reference_range_3.yml',
        ];
        $objects = Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);

        $this->assertCount(11, $objects);

        $groupReference = $objects['group_1_user1'];
        $this->assertInstanceOf(self::GROUP, $groupReference);
        $this->assertInstanceOf(self::USER, $groupReference->getOwner());
        $this->assertCount(3, $groupReference->getMembers());

        $groupReferenceList1 = $objects['group_list_user1'];
        $this->assertInstanceOf(self::GROUP, $groupReferenceList1);
        $this->assertInstanceOf(self::USER, $groupReferenceList1->getOwner());
        $this->assertEquals($objects['user1'], $groupReferenceList1->getOwner());

        $groupReferenceList2 = $objects['group_list_user2'];
        $this->assertInstanceOf(self::GROUP, $groupReferenceList2);
        $this->assertInstanceOf(self::USER, $groupReferenceList2->getOwner());
        $this->assertEquals($objects['user2'], $groupReferenceList2->getOwner());

        $groupReferenceList3 = $objects['group_list_user3'];
        $this->assertInstanceOf(self::GROUP, $groupReferenceList3);
        $this->assertInstanceOf(self::USER, $groupReferenceList3->getOwner());
        $this->assertEquals($objects['user3'], $groupReferenceList3->getOwner());

        $taskGroupUser1 = $objects['task_1_group_1_user1'];
        $this->assertInstanceOf(self::TASK, $taskGroupUser1);

        $taskList1 = $objects['task_list_group_list_user1'];
        $this->assertInstanceOf(self::TASK, $taskList1);
    }

    /**
     *
     */
    public function testLoadFixturesByReferenceWithRangeList()
    {
        $managerMock = $this->getDoctrineManagerMock(5);

        $managerMock->expects($this->exactly(2))
            ->method('flush');

        $files = [
            __DIR__ . '/support/fixtures/reference_range_with_range_list_1.yml',
            __DIR__ . '/support/fixtures/reference_range_with_range_list_2.yml',
        ];
        $objects = Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);

        $this->assertCount(5, $objects);

        $groupReferenceUserAlice = $objects['group_user_alice'];
        $this->assertInstanceOf(self::GROUP, $groupReferenceUserAlice);
        $this->assertInstanceOf(self::USER, $groupReferenceUserAlice->getOwner());
        $this->assertEquals($objects['user_alice'], $groupReferenceUserAlice->getOwner());

        $groupReferenceUserBob = $objects['group_user_bob'];
        $this->assertInstanceOf(self::GROUP, $groupReferenceUserBob);
        $this->assertInstanceOf(self::USER, $groupReferenceUserBob->getOwner());
        $this->assertEquals($objects['user_bob'], $groupReferenceUserBob->getOwner());

    }

    /**
     * @param array|null $objects
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDoctrineManagerMock($objects = null)
    {
        $managerMock = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $managerMock->expects($objects ? $this->exactly($objects) : $this->any())
            ->method('persist');

        return $managerMock;
    }

}
