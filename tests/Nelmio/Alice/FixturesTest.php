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

use Nelmio\Alice\fixtures\User;

class FixturesTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\fixtures\User';
    const GROUP = 'Nelmio\Alice\fixtures\Group';

    public function testLoadLoadsYamlFilesAndDoctrineORM()
    {
        $om = $this->getDoctrineManagerMock(13);
        $objects = Fixtures::load(__DIR__.'/fixtures/complete.yml', $om);

        $this->assertCount(13, $objects);

        $user = $objects[0];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('johnny', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);

        $group = end($objects);
        $this->assertInstanceOf(self::GROUP, $group);
        $this->assertCount(3, $group->getMembers());
    }

    public function testLoadLoadsArrays()
    {
        $om = $this->getDoctrineManagerMock(2);

        $objects = Fixtures::load(array(
            self::USER => array(
                'user1' => array(
                    'username' => 'johnny',
                    'favoriteNumber' => 42,
                ),
            ),
            self::GROUP => array(
                'group1' => array(
                    'owner' => 1
                ),
            ),

        ), $om);

        $this->assertCount(2, $objects);

        $user = $objects[0];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('johnny', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);
    }

    public function testLoadLoadsPHPfiles()
    {
        $om = $this->getDoctrineManagerMock(2);

        $objects = Fixtures::load(__DIR__.'/fixtures/basic.php', $om);

        $this->assertCount(2, $objects);

        $user = $objects[0];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('johnny', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);
    }

    protected function getDoctrineManagerMock($objects = null)
    {
        $om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $om->expects($objects ? $this->exactly($objects) : $this->any())
            ->method('persist');

        $om->expects($this->once())
            ->method('flush');

        $om->expects($this->once())
            ->method('find')->will($this->returnValue(new User()));

        return $om;
    }
}
