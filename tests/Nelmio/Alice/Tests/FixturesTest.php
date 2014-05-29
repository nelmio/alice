<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Tests;

use Nelmio\Alice\fixtures\User;
use Nelmio\Alice\Fixtures;
use Nelmio\Alice\Mocks\FooProvider;
use Nelmio\Alice\Persister\PersisterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FixturesTest extends \PHPUnit_Framework_TestCase
{
    const USER = 'Nelmio\Alice\fixtures\User';
    const GROUP = 'Nelmio\Alice\fixtures\Group';
    const CONTACT = 'Nelmio\Alice\fixtures\Contact';

    public function testLoadLoadsYamlFilesAndDoctrineORM()
    {
        $persister = $this->getMockPersister(14);
        $objects = Fixtures::load(__DIR__.'/../fixtures/complete.yml', $persister, $this->getMockDispatcher(), array('providers' => array($this)));

        $this->assertCount(14, $objects);

        $user = $objects['user0'];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('johnny', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);

        $user = $objects['user0'];
        $group = $objects['group0'];
        $this->assertSame($user, $group->getOwner());

        $lastGroup = $objects['group1'];
        $this->assertInstanceOf(self::GROUP, $lastGroup);
        $this->assertCount(3, $lastGroup->getMembers());

        $contact = $objects['contact0'];
        $this->assertInstanceOf(self::CONTACT, $contact);
        $this->assertSame($user, $contact->getUser());
        $this->assertSame($lastGroup->contactPerson, $contact->getUser());
    }

    public function testThatNewLoaderIsCreatedForDifferingOptions()
    {
        $persister = $this->getMockPersister();

        $optionsBatch = array(
            // default options
            array(),
            // full list
            array(
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => array(
                    'Nelmio\Alice\Mocks\FooProvider'
                )
            ),
            // check that loader isn't created twice for the same options
            array(
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => array(
                    new FooProvider()
                )
            ),
            // check that loader isn't created twice for the same options
            array(
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => array(
                    // this time we have the leading backslash
                    '\Nelmio\Alice\Mocks\FooProvider'
                )
            ),
            // check that a new loader will be created for the same options
            // when the format of fixtures is different
            array(
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => array(
                    'Nelmio\Alice\Mocks\FooProvider'
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
                    'Nelmio\Alice\Mocks\BarProvider'
                ),
            ),
            array(
                'locale'    => 'ja_JP',
                'seed'      => 3,
                'providers' => array(
                    'Nelmio\Alice\Mocks\FooProvider',
                    'Nelmio\Alice\Mocks\BarProvider'
                ),
            ),
            array(
                'locale'    => 'ru_RU',
                'seed'      => 1,
                'providers' => array(
                    'Nelmio\Alice\Mocks\BarProvider'
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
                    'Nelmio\Alice\Mocks\BarProvider'
                )
            ),
            array(
                'locale'    => 'fr_FR',
                'seed'      => null,
                'providers' => array(
                    'Nelmio\Alice\Mocks\FooProvider'
                )
            ),
        );

        foreach ($optionsBatch as $item) {
            $fixtures = isset($item['fixtures']) ?: __DIR__.'/../fixtures/complete.yml';

            if (!isset($item['providers'])) {
                $item['providers'] = array();
            }

            $item['providers'][] = $this;

            Fixtures::load($fixtures, $persister, $this->getMockDispatcher(), $item);
        }

        $prop = new \ReflectionProperty('\Nelmio\Alice\Fixtures', 'loaders');
        $prop->setAccessible(true);

        $loaders = $prop->getValue();

        $this->assertEquals(12, count($loaders));
    }

    public function testThatExceptionIsThrownForInvalidProvider()
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            'The provider should be a string or an object, got array instead'
        );

        Fixtures::load(
            __DIR__.'/../fixtures/complete.yml',
            $this->getMockPersister(),
            $this->getMockDispatcher(),
            array(
                'providers' => array(
                    'Nelmio\Alice\Mocks\FooProvider',
                    array('foo'),
                    $this,
                ),
            )
        );
    }

    public function testLoadLoadsYamlFilesAsArray()
    {
        $persister = $this->getMockPersister();
        $objects = Fixtures::load(array(__DIR__.'/../fixtures/complete.yml'), $persister, $this->getMockDispatcher(), array('providers' => array($this)));

        $this->assertCount(14, $objects);
    }

    public function testLoadLoadsYamlFilesAsGlobString()
    {
        $persister = $this->getMockPersister();
        $objects = Fixtures::load(__DIR__.'/../fixtures/complete.y*', $persister, $this->getMockDispatcher(), array('providers' => array($this)));

        $this->assertCount(14, $objects);
    }

    public function testLoadLoadsArrays()
    {
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

        ), $this->getMockPersister(), $this->getMockDispatcher());

        $this->assertCount(2, $objects);

        $user = $objects['user1'];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('johnny', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);
    }

    public function testLoadLoadsPHPfiles()
    {
        $persister = $this->getMockPersister();
        $objects = Fixtures::load(__DIR__.'/../fixtures/basic.php', $persister, $this->getMockDispatcher());

        $this->assertCount(2, $objects);

        $user = $objects['user1'];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('johnny', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);
    }

    public function testMakesOnlyOneFlushWithPersistOnce()
    {
        $objects = Fixtures::load(
            array(
                __DIR__.'/../fixtures/part_1.yml',
                __DIR__.'/../fixtures/part_2.yml',
            ),
            $this->getMockPersister(),
            $this->getMockDispatcher(),
            array(
                'providers' => array($this),
                'persist_once' => true
            )
        );

        $this->assertCount(19, $objects);

        $user = $objects['user11'];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('John Doe', $user->fullname);
        $this->assertNotEquals(127, $user->favoriteNumber);

        $user = $objects['user12'];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('stormtrooper12', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);

        $user = $objects['user15'];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('stormtrooper15', $user->username);
    }

    /**
     * @return PersisterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockPersister($persist = false)
    {
        $persister = $this->getMockForAbstractClass('Nelmio\Alice\Persister\PersisterInterface');

        if ($persist) {
            $persister
                ->expects($this->once())
                ->method('persist')
            ;
        }

        $persister
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue(new User()))
        ;

        return $persister;
    }


    /**
     * @return EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockDispatcher()
    {
        return $this->getMockForAbstractClass('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * Custom provider for the complete.yml file
     */
    public function contactName($user)
    {
        return $user->username;
    }
}
