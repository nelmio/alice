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

/**
 * @package Nelmio\Alice
 */
class ReferenceRangeNameTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\support\models\User';
    const GROUP = 'Nelmio\Alice\support\models\Group';
    const TASK = 'Nelmio\Alice\support\models\Task';

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function throwExceptionWhenReferencesAreNotFound()
    {
        $managerMock = $this->getDoctrineManagerMock();

        $files = [
            __DIR__ . '/support/fixtures/reference_range_2.yml',
        ];

        Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);
    }

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function throwExceptionWhenSelfReferencesAreNotFound()
    {
        $managerMock = $this->getDoctrineManagerMock();

        $files = [
            __DIR__ . '/support/fixtures/reference_range_with_self_reference.yml',
        ];

        Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);
    }

    /**
     * @test
     */
    public function loadFixturesByReference()
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
        $this->assertInstanceOf(self::GROUP, $groupReference);
        $this->assertInstanceOf(self::USER, $groupReference->getOwner());
        $this->assertEquals($objects['user1'], $groupReference->getOwner());
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
     * @test
     */
    public function loadFixturesByReferenceWithRangeList()
    {
        $managerMock = $this->getDoctrineManagerMock();

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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDoctrineManagerMock()
    {
        $managerMock = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $metadataFactoryMock = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadataFactory');
        $metadataMock = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $managerMock->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactoryMock));

        $metadataFactoryMock->method('getAllMetadata')
            ->will($this->returnValue([$metadataMock]));

        $managerMock->method('flush');
        $managerMock->method('persist');

        return $managerMock;
    }

}
