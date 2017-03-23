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
use Nelmio\Alice\support\models\User;
use PHPUnit\Framework\TestCase;

class FixturesTest extends TestCase
{
    const USER = 'Nelmio\Alice\support\models\User';
    const GROUP = 'Nelmio\Alice\support\models\Group';
    const CONTACT = 'Nelmio\Alice\support\models\Contact';

    public function testLoadUniqueFromTemplateFixtures()
    {
        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)->getMock();
        $metadataFactory = $this->getMockBuilder(ClassMetadataFactory::class)->getMock();
        $metadata = $this->getMockBuilder(ClassMetadata::class)->getMock();

        $objectManagerMock->expects($this->any())
            ->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactory));

        $metadataFactory->expects($this->any())
            ->method('getAllMetadata')
            ->will($this->returnValue([$metadata, $metadata, $metadata]));

        $objects = Fixtures::load(__DIR__.'/support/fixtures/unique_with_template.yml', $objectManagerMock, ['providers' => [$this]]);

        $this->assertCount(26, $objects);

        $names = [];
        foreach($objects as $object) {
            $this->assertNotNull($object->fullname, 'fullname should not be null');
            $this->assertFalse(in_array($object->username, $names), sprintf('duplicate value %s', $object->username));
            $names[] = $object->username;
        }
    }

    public function testLoadUniqueFromMoreThanOneTemplateFixtures()
    {
        $objectManagerMock = $this->getMockBuilder(ObjectManager::class)->getMock();
        $metadataFactory = $this->getMockBuilder(ClassMetadataFactory::class)->getMock();
        $metadata = $this->getMockBuilder(ClassMetadata::class)->getMock();

        $objectManagerMock->expects($this->any())
            ->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactory));

        $metadataFactory->expects($this->any())
            ->method('getAllMetadata')
            ->will($this->returnValue([$metadata, $metadata, $metadata]));

        $objects = Fixtures::load(__DIR__.'/support/fixtures/unique_with_more_templates.yml', $objectManagerMock, ['providers' => [$this], 'seed' => 2]);

        $this->assertCount(26, $objects);

        $usernames = [];
        $fullnames = [];

        foreach($objects as $object) {

            $this->assertContains($object->email, ['A','B','C','D','E','F']);
            $this->assertFalse(in_array($object->username, $usernames), sprintf('duplicate username value %s', $object->username));
            $this->assertFalse(in_array($object->fullname, $fullnames), sprintf('duplicate fullname value %s', $object->fullname));

            $usernames[] = $object->username;
            $fullnames[] = $object->fullname;
        }
    }

    public function testLoadLoadsYamlFilesAndDoctrinePersister()
    {
        $om = $this->getDoctrineManagerMock(14);
        $objects = Fixtures::load(__DIR__.'/support/fixtures/complete.yml', $om, ['providers' => [$this]]);

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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testLoadFailsOnMissingFiles()
    {
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();
        $objects = Fixtures::load(__DIR__.'/fixtures/missing_file.yml', $om, ['providers' => [$this]]);
    }

    public function testThatNewLoaderIsCreatedForDifferingOptions()
    {
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();
        $metadataFactory = $this->getMockBuilder(ClassMetadataFactory::class)->getMock();
        $metadata = $this->getMockBuilder(ClassMetadata::class)->getMock();

        $om->expects($this->any())
            ->method('find')->will($this->returnValue(new User()));

        $om->expects($this->any())
            ->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactory));

        $metadataFactory->expects($this->any())
            ->method('getAllMetadata')
            ->will($this->returnValue([$metadata, $metadata, $metadata]));

        $prop = new \ReflectionProperty('\Nelmio\Alice\Fixtures', 'loaders');
        $prop->setAccessible(true);
        $prop->setValue([]);

        $optionsBatch = [
            // default options
            [],
            // full list
            [
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => [
                    'Nelmio\Alice\FooProvider'
                ]
            ],
            // check that loader isn't created twice for the same options
            [
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => [
                    new \Nelmio\Alice\FooProvider()
                ]
            ],
            // check that loader isn't created twice for the same options
            [
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => [
                    // this time we have the leading backslash
                    '\Nelmio\Alice\FooProvider'
                ]
            ],
            // check that a new loader will be created for the same options
            // when the format of fixtures is different
            [
                'locale'    => 'en_US',
                'seed'      => 1,
                'providers' => [
                    'Nelmio\Alice\FooProvider'
                ]
            ],
            // check various combinations of options (non-exhaustive)
            [
                'locale'    => 'ja_JP',
                'seed'      => 3,
                'providers' => [
                    'Nelmio\Alice\BarProvider'
                ],
            ],
            [
                'locale'    => 'ja_JP',
                'seed'      => 3,
                'providers' => [
                    'Nelmio\Alice\FooProvider',
                    'Nelmio\Alice\BarProvider'
                ],
            ],
            [
                'locale'    => 'ru_RU',
                'seed'      => 1,
                'providers' => [
                    'Nelmio\Alice\BarProvider'
                ]
            ],
            [
                'locale'    => 'ru_RU',
                'seed'      => 100,
            ],
            [
                'locale'    => 'ru_RU',
                'seed'      => null,
            ],
            [
                'locale'    => 'de_DE',
            ],
            [
                'locale'    => 'fr_FR',
                'seed'      => null,
                'providers' => [
                    'Nelmio\Alice\BarProvider'
                ]
            ],
            [
                'locale'    => 'fr_FR',
                'seed'      => null,
                'providers' => [
                    'Nelmio\Alice\FooProvider'
                ]
            ],
        ];

        foreach ($optionsBatch as $item) {
            $fixtures = isset($item['fixtures'])
                        ? $item['fixtures']
                        : __DIR__.'/support/fixtures/complete.yml';
            if (!isset($item['providers'])) {
                $item['providers'] = [];
            }
            $item['providers'][] = $this;
            Fixtures::load(
                $fixtures,
                $om,
                $item
            );
        }

        $loaders = $prop->getValue();

        $this->assertEquals(10, count($loaders));
    }

    public function testThatExceptionIsThrownForInvalidProvider()
    {
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();
        $om->expects($this->any())
            ->method('find')->will($this->returnValue(new User()));

        $this->expectException(
            '\InvalidArgumentException',
            'The provider should be a string or an object, got array instead'
        );

        Fixtures::load(
            __DIR__.'/support/fixtures/complete.yml',
            $om,
            [
                'providers' => [
                    'Nelmio\Alice\FooProvider',
                    ['foo'],
                    $this,
                ],
            ]
        );
    }

    public function testLoadLoadsYamlFilesAsArray()
    {
        $om = $this->getDoctrineManagerMock(14);
        $objects = Fixtures::load([__DIR__.'/support/fixtures/complete.yml'], $om, ['providers' => [$this]]);

        $this->assertCount(14, $objects);
    }

    public function testLoadLoadsYamlFilesAsGlobString()
    {
        $om = $this->getDoctrineManagerMock(14);
        $objects = Fixtures::load(__DIR__.'/support/fixtures/complete.y*', $om, ['providers' => [$this]]);

        $this->assertCount(14, $objects);
    }

    public function testLoadLoadsArrays()
    {
        $om = $this->getDoctrineManagerMock(2);

        $objects = Fixtures::load([
            self::USER => [
                'user1' => [
                    'username' => 'johnny',
                    'favoriteNumber' => 42,
                ],
            ],
            self::GROUP => [
                'group1' => [
                    'owner' => 1
                ],
            ],

        ], $om);

        $this->assertCount(2, $objects);

        $user = $objects['user1'];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('johnny', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);
    }

    public function testLoadLoadsPHPfiles()
    {
        $om = $this->getDoctrineManagerMock(2);

        $objects = Fixtures::load(__DIR__.'/support/fixtures/basic.php', $om);

        $this->assertCount(2, $objects);

        $user = $objects['user1'];
        $this->assertInstanceOf(self::USER, $user);
        $this->assertEquals('johnny', $user->username);
        $this->assertEquals(42, $user->favoriteNumber);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testLoadWithLogger()
    {
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();

        Fixtures::load(__DIR__.'/support/fixtures/basic.php', $om, [
            'logger' => function () {}
        ]);

        try {
            Fixtures::load(__DIR__.'/support/fixtures/basic.php', $om, [
                'logger' => 'not callable'
            ]);
        } catch (\RuntimeException $exception) {
            // Expected result
        }
    }

    public function testMakesOnlyOneFlushWithPersistOnce()
    {
        $om = $this->getDoctrineManagerMock(19);
        $objects = Fixtures::load(
            [
                __DIR__.'/support/fixtures/part_1.yml',
                __DIR__.'/support/fixtures/part_2.yml',
            ],
            $om,
            [
                'providers' => [$this],
                'persist_once' => true
            ]
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

    protected function getDoctrineManagerMock($objects = null)
    {
        $om = $this->getMockBuilder(ObjectManager::class)->getMock();
        $metadataFactory = $this->getMockBuilder(ClassMetadataFactory::class)->getMock();
        $metadata1 = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $metadata2 = $this->getMockBuilder(ClassMetadata::class)->getMock();
        $metadata3 = $this->getMockBuilder(ClassMetadata::class)->getMock();

        $om->expects($this->once())
            ->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactory));

        $metadataFactory->expects($this->once())
            ->method('getAllMetadata')
            ->will($this->returnValue([$metadata1, $metadata2, $metadata3]));

        $metadata1->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(self::USER));

        $metadata2->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(self::CONTACT));

        $metadata3->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(self::GROUP));

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
