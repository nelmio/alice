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
    const CONTACT = 'Nelmio\Alice\fixtures\Contact';

    public function testLoadLoadsYamlFilesAndDoctrineORM()
    {
        $om = $this->getDoctrineManagerMock(14);
        $objects = Fixtures::load(__DIR__.'/fixtures/complete.yml', $om, array('providers' => array($this)));
		$objects = array_values($objects);
		
        $this->assertCount(14, $objects);

        $user = $objects[0];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('johnny', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);

        $user = $objects[0];
        $group = $objects[12];
        $this->assertSame($user, $group->getOwner());

        $lastGroup = end($objects);
        $this->assertInstanceOf(self::GROUP, $lastGroup);
        $this->assertCount(3, $lastGroup->getMembers());

        $contact = $objects[11];
        $this->assertInstanceOf(self::CONTACT, $contact);
        $this->assertSame($user, $contact->getUser());
        $this->assertSame($lastGroup->contactPerson, $contact->getUser());
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
                    new \Nelmio\Alice\FooProvider()
                )
            ),
            // check that loader isn't created twice for the same options
            array(
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => array(
                    // this time we have the leading backslash
                    '\Nelmio\Alice\FooProvider'
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
            if (!isset($item['providers'])) {
                $item['providers'] = array();
            }
            $item['providers'][] = $this;
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

    public function testThatExceptionIsThrownForInvalidProvider()
    {
        $om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $om->expects($this->any())
            ->method('find')->will($this->returnValue(new User()));

        $this->setExpectedException(
            '\InvalidArgumentException',
            'The provider should be a string or an object, got array instead'
        );

        Fixtures::load(
            __DIR__.'/fixtures/complete.yml',
            $om,
            array(
                'providers' => array(
                    'Nelmio\Alice\FooProvider',
                    array('foo'),
                    $this,
                ),
            )
        );
    }

    public function testLoadLoadsYamlFilesAsArray()
    {
        $om = $this->getDoctrineManagerMock(14);
        $objects = Fixtures::load(array(__DIR__.'/fixtures/complete.yml'), $om, array('providers' => array($this)));

        $this->assertCount(14, $objects);
    }

    public function testLoadLoadsYamlFilesAsGlobString()
    {
        $om = $this->getDoctrineManagerMock(14);
        $objects = Fixtures::load(__DIR__.'/fixtures/complete.y*', $om, array('providers' => array($this)));

        $this->assertCount(14, $objects);
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
		$objects = array_values($objects);
        
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
		$objects = array_values($objects);
        
        $this->assertCount(2, $objects);

        $user = $objects[0];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('johnny', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoadWithLogger()
    {
        $om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');

        $objects = Fixtures::load(__DIR__.'/fixtures/basic.php', $om, array(
            'logger' => 'not callable'
        ));
    }

    public function testMakesOnlyOneFlushWithPersistOnce()
    {
        $om = $this->getDoctrineManagerMock(14);
        $objects = Fixtures::load(
            array(
                __DIR__.'/fixtures/part_1.yml',
                __DIR__.'/fixtures/part_2.yml',
            ),
            $om,
            array(
                'providers' => array($this),
                'persist_once' => true
            )
        );

        $this->assertCount(14, $objects);
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

    /**
     * Custom provider for the complete.yml file
     */
    public function contactName($user)
    {
        return $user->username;
    }
}
