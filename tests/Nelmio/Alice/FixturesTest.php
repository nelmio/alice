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

        $user = $objects[0];
        $group = $objects[11];
        $this->assertSame($user, $group->getOwner());

        $group = end($objects);
        $this->assertInstanceOf(self::GROUP, $group);
        $this->assertCount(3, $group->getMembers());
    }

    public function testThatNewLoaderIsCreatedForDifferingOptions()
    {
        $om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $om->expects($this->any())
            ->method('find')->will($this->returnValue(new User()));

        $optionsBatch = array(
            // default options
            array(),
            // full list 
            array(
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => array(
                    'Nelmio\Alice\FooProvider'
                )
            ),
            // check that loader isn't created twice for the same options
            array(
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => array(
                    'Nelmio\Alice\FooProvider'
                )
            ),
            // check that a new loader will be created for the same options
            // when the format of fixtures is different
            array(
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => array(
                    'Nelmio\Alice\FooProvider'
                ),
                'fixtures' => array(
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
                ),
            ),
            // check various combinations of options (non-exhaustive)
            array(
                'locale'    => 'ja_JP',
                'seed'      => 3,
                'providers' => array(
                    'Nelmio\Alice\BarProvider'
                ),
            ),
            array(
                'locale'    => 'ja_JP',
                'seed'      => 3,
                'providers' => array(
                    'Nelmio\Alice\FooProvider',
                    'Nelmio\Alice\BarProvider'
                ),
            ),
            array(
                'locale'    => 'ru_RU',
                'seed'      => 1,
                'providers' => array(
                    'Nelmio\Alice\BarProvider'
                )
            ),
            array(
                'locale'    => 'ru_RU',
                'seed'      => 100,
            ),
            array(
                'locale'    => 'ru_RU',
                'seed'      => null,
            ),
            array(
                'locale'    => 'de_DE',
                'fixtures' => array(
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
                ),
            ),
            array(
                'locale'    => 'de_DE',
            ),
            array(
                'locale'    => 'fr_FR',
                'seed'      => null,
                'providers' => array(
                    'Nelmio\Alice\BarProvider'
                )
            ),
            array(
                'locale'    => 'fr_FR',
                'seed'      => null,
                'providers' => array(
                    'Nelmio\Alice\FooProvider'
                )
            ),
        );

        foreach ($optionsBatch as $item) {
            $fixtures = isset($item['fixtures'])
                        ? isset($item['fixtures'])
                        : __DIR__.'/fixtures/complete.yml';
            Fixtures::load(
                $fixtures,
                $om,
                $item
            );
        }

        $prop = new \ReflectionProperty('\Nelmio\Alice\Fixtures', 'loaders');
        $prop->setAccessible(true);
        $loaders = $prop->getValue();

        $this->assertEquals(12, count($loaders));
    }

    public function testLoadLoadsYamlFilesAsArray()
    {
        $om = $this->getDoctrineManagerMock(13);
        $objects = Fixtures::load(array(__DIR__.'/fixtures/complete.yml'), $om);

        $this->assertCount(13, $objects);
    }

    public function testLoadLoadsYamlFilesAsGlobString()
    {
        $om = $this->getDoctrineManagerMock(13);
        $objects = Fixtures::load(__DIR__.'/fixtures/complete.y*', $om);

        $this->assertCount(13, $objects);
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
