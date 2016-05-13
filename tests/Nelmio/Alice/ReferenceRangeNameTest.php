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
    const CONTACT = 'Nelmio\Alice\support\models\Contact';

    /**
     * @test
     * @expectedException \UnexpectedValueException
     */
    public function loadFixturesByReferenceNotFound()
    {
        $managerMock = $this->getDoctrineManagerMock(null);

        $files = [
            __DIR__ . '/support/fixtures/reference_range_2.yml',
        ];

        Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);
    }

    /**
     * @test
     */
    public function loadFixturesByReference()
    {
        $managerMock = $this->getDoctrineManagerMock(7);

        $managerMock->expects($this->exactly(2))
            ->method('flush');

        $files = [
            __DIR__ . '/support/fixtures/reference_range_1.yml',
            __DIR__ . '/support/fixtures/reference_range_2.yml',
        ];
        $objects = Fixtures::load($files, $managerMock, [ 'providers' => [ $this ] ]);

        $this->assertCount(7, $objects);

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
