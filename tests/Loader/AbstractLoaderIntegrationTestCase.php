<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Loader;

use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\Entity\DummyWithConstructorParam;
use Nelmio\Alice\Entity\DummyWithPublicProperty;
use Nelmio\Alice\Entity\Hydrator\CamelCaseDummy;
use Nelmio\Alice\Entity\Hydrator\MagicCallDummy;
use Nelmio\Alice\Entity\Hydrator\PascalCaseDummy;
use Nelmio\Alice\Entity\Hydrator\SnakeCaseDummy;
use Nelmio\Alice\Entity\Instantiator\DummyWithDefaultConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithExplicitDefaultConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithNamedConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithNamedConstructorAndOptionalParameters;
use Nelmio\Alice\Entity\Instantiator\DummyWithNamedConstructorAndRequiredParameters;
use Nelmio\Alice\Entity\Instantiator\DummyWithNamedPrivateConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithOptionalParameterInConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithPrivateConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithProtectedConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithRequiredParameterInConstructor;
use Nelmio\Alice\Entity\StdClassFactory;
use Nelmio\Alice\Entity\ValueResolver\DummyWithGetter;
use Nelmio\Alice\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\GenerationThrowable;
use Nelmio\Alice\Throwable\HydrationThrowable;
use Nelmio\Alice\Throwable\InstantiationThrowable;

/**
 * @group integration
 */
abstract class AbstractLoaderIntegrationTestCase extends \PHPUnit_Framework_TestCase
{
    const PARSER_FILES_DIR = __DIR__.'/../../fixtures/Parser/files';
    const FIXTURES_FILES_DIR = __DIR__.'/../../fixtures/Integration';

    /**
     * @var FileLoaderInterface|DataLoaderInterface
     */
    private $loader;

    public function setUp()
    {
        $this->loader = $this->getLoader();
    }

    /**
     * @return FileLoaderInterface|DataLoaderInterface
     */
    public abstract function getLoader();

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The file "unknown.yml" could not be found.
     */
    public function testLoadInexistingFile()
    {
        $this->loader->loadFile('unknown.yml');
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Parser\ParserNotFoundException
     * @expectedExceptionMessageRegExp /^No suitable parser found for the file ".*?plain_file"\.$/
     */
    public function testLoadUnsupportedFileFormat()
    {
        $this->loader->loadFile(self::PARSER_FILES_DIR.'/unsupported/plain_file');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^The file ".*?no_return.php" must return a PHP array\.$/
     */
    public function testLoadPhpFileWhichDoesNotReturnAnything()
    {
        $this->loader->loadFile(self::PARSER_FILES_DIR.'/php/no_return.php');
    }

    public function testLoadEmptyData()
    {
        $set = $this->loader->loadData([]);

        $this->assertEquals(
            new ObjectSet(new ParameterBag(), new ObjectBag()),
            $set
        );
    }

    /**
     * @dataProvider provideFixturesToInstantiate
     */
    public function testObjectInstantiation(array $data, $expected)
    {
        try {
            $objects = $this->loader->loadData($data)->getObjects();
            if (null === $expected) {
                $this->fail('Expected exception to be thrown.');
            }
        } catch (InstantiationThrowable $exception) {
            if (null === $expected) {
                return;
            }

            throw $exception;
        }

        $this->assertCount(1, $objects);
        $this->assertEquals($expected, $objects['dummy']);
    }

    /**
     * @dataProvider provideFixturesToHydrate
     */
    public function testObjectHydration(array $data, array $expected = null)
    {
        try {
            $objects = $this->loader->loadData($data)->getObjects();
            if (null === $expected) {
                $this->fail('Expected exception to be thrown.');
            }
        } catch (HydrationThrowable $exception) {
            if (null === $expected) {
                return;
            }

            throw $exception;
        }

        $this->assertEquals(count($expected), count($objects));
        $this->assertEquals($expected, $objects);
    }

    /**
     * @dataProvider provideFixturesToGenerate
     */
    public function testFixtureGeneration(array $data, array $expected = null)
    {
        try {
            $set = $this->loader->loadData($data);
            if (null === $expected) {
                $this->fail('Expected exception to be thrown.');
            }
        } catch (GenerationThrowable $exception) {
            if (null === $expected) {
                return;
            }

            throw $exception;
        }

        $expectedParameters = $expected['parameters'];
        $actualParameters = $set->getParameters();
        $this->assertEquals(count($expectedParameters), count($actualParameters));
        $this->assertEquals($expectedParameters, $actualParameters);

        $expectedObjects = $expected['objects'];
        $actualObjects = $set->getObjects();
        $this->assertEquals(count($expectedObjects), count($actualObjects));
        $this->assertEquals($expectedObjects, $actualObjects);
    }

    public function testLoadASetOfDataWithInjectedObjects()
    {
        $set = $this->loader->loadData(
            [
                \stdClass::class => [
                    'dummy' => [
                        'relatedDummy' => '@injected_dummy',
                    ],
                ],
            ],
            [],
            [
                'injected_dummy' => StdClassFactory::create(['injected' => true])
            ]
        );
        $objects = $set->getObjects();

        $this->assertCount(0, $set->getParameters());
        $this->assertCount(2, $objects);

        $this->assertEquals(
            [
                'injected_dummy' => $injectedDummy = StdClassFactory::create(['injected' => true]),
                'dummy' => StdClassFactory::create(['relatedDummy' => $injectedDummy]),
            ],
            $objects
        );
    }

    public function testIfAFixtureAndAnInjectedObjectHaveTheSameIdThenTheInjectedObjectIsOverridden()
    {
        $set = $this->loader->loadData(
            [
                \stdClass::class => [
                    'dummy' => [
                        'injected' => false,
                    ],
                    'dummy_with_constructor' => [
                        '__construct' => [
                            StdClassFactory::class.'::create' => [['injected' => false]],
                        ],
                        'injected' => false,
                    ],
                ],
            ],
            [],
            [
                'dummy' => StdClassFactory::create(['injected' => true]),
                'dummy_with_constructor' => StdClassFactory::create(['injected' => true]),
            ]
        );
        $objects = $set->getObjects();

        $this->assertCount(0, $set->getParameters());
        $this->assertCount(2, $objects);

        $this->assertEquals(
            [
                'dummy' => StdClassFactory::create(['injected' => false]),
                'dummy_with_constructor' => StdClassFactory::create(['injected' => false]),
            ],
            $objects
        );
    }

    public function testLoadOptionalValues()
    {
        $data = [
            \stdClass::class => [
                'user0' => [
                    'username' => '80%? something',
                ],
                'user1' => [
                    'username' => '80%? something : nothing',
                ],
                'user2' => [
                    'username' => '0%? something : nothing',
                ],
                'user3' => [
                    'username' => '100%? something : nothing',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        $this->assertEquals(0, count($set->getParameters()));

        $objects = $set->getObjects();
        $this->assertEquals(4, count($objects));

        $this->assertContains($objects['user0']->username, ['something', null]);
        $this->assertContains($objects['user1']->username, ['something', 'nothing']);
        $this->assertEquals('nothing', $objects['user2']->username);
        $this->assertEquals('something', $objects['user3']->username);
    }

    public function testLoadTwoSuccessiveFakerFunctions()
    {
        $data = [
            \stdClass::class => [
                'user' => [
                    'username' => '<firstName()> <lastName()>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        $this->assertEquals(0, count($set->getParameters()));

        $objects = $set->getObjects();
        $this->assertEquals(1, count($objects));

        $user = $objects['user'];
        $this->assertInstanceOf(\stdClass::class, $user);
        $this->assertRegExp('/^[\w\']+ [\w\']+$/i', $user->username);
    }

    public function testLoadFakerFunctionWithData()
    {
        $data = [
            \stdClass::class => [
                'user' => [
                    'age' => '<numberBetween(10, 10)>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        $this->assertEquals(0, count($set->getParameters()));

        $objects = $set->getObjects();
        $this->assertEquals(1, count($objects));

        $user = $objects['user'];
        $this->assertInstanceOf(\stdClass::class, $user);
        $this->assertTrue(10 === $user->age);
    }

    public function testLoadLocalizedFakerFunctionWithData()
    {
        $data = [
            \stdClass::class => [
                'user' => [
                    'siren' => '<fr_FR:siren()>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        $this->assertEquals(0, count($set->getParameters()));

        $objects = $set->getObjects();
        $this->assertEquals(1, count($objects));

        $user = $objects['user'];
        $this->assertInstanceOf(\stdClass::class, $user);
        $this->assertRegExp('/^\d{3} \d{3} \d{3}$/', $user->siren);
    }

    public function testLoadFakerFunctionWithPhpArguments()
    {
        $this->markTestIncomplete('TODO, see https://github.com/nelmio/alice/issues/498#issuecomment-242488332');
        $data = [
            \stdClass::class => [
                'user' => [
                    'updatedAt' => '<dateTimeBetween(<("yest"."erday")>, <(strrev("omot")."rrow"))>>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        $this->assertEquals(0, count($set->getParameters()));

        $objects = $set->getObjects();
        $this->assertEquals(1, count($objects));

        $user = $objects['user'];
        $this->assertInstanceOf(\stdClass::class, $user);

        $updatedAt = $user->updatedAt;
        $this->assertInstanceOf(\DateTimeInterface::class, $updatedAt);
        /** @var \DateTimeInterface $updatedAt */
        $this->assertGreaterThanOrEqual(strtotime('yesterday'), $updatedAt->getTimestamp());
        $this->assertLessThanOrEqual(strtotime('tomorrow'), $updatedAt->getTimestamp());
    }

    public function testLoadSelfReferencedFixture()
    {
        $data = [
            \stdClass::class => [
                'dummy' => [
                    'relatedDummy' => '@dummy*',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        $this->assertEquals(0, count($set->getParameters()));

        $objects = $set->getObjects();
        $this->assertEquals(1, count($objects));

        $expectedDummy = new \stdClass();
        $expectedDummy->relatedDummy = $expectedDummy;

        $this->assertEquals($expectedDummy, $objects['dummy']);
    }

    public function testLoadSelfReferencedFixtures()
    {
        $data = [
            \stdClass::class => [
                'dummy{1..2}' => [
                    'relatedDummies' => '3x @dummy*',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        $this->assertEquals(0, count($set->getParameters()));

        $objects = $set->getObjects();
        $this->assertEquals(2, count($objects));
    }

    public function testTemplatesAreKeptBetweenFiles()
    {
        $expected = new ObjectSet(
            new ParameterBag(),
            new ObjectBag([
                'dummy' => StdClassFactory::create([
                    'foo' => 'bar',
                ]),
            ])
        );
        $actual = $this->loader->loadFile(self::FIXTURES_FILES_DIR.'/template_in_another_file/dummy.yml');

        $this->assertEquals($expected, $actual);
    }

    public function testTemplateCanExtendOtherTemplateObjectsCombinedWithRange()
    {
        $data = [
            \stdClass::class => [
                'base_{du, yu}mmy (template)' => [
                    'base' => 'true',
                ],
                'dummy{1..2} (template, extends base_dummy)' => [
                    'foo' => 'bar',
                ],
                'y{u, U}mmy (extends dummy2)' => [
                    'foo' => 'baz',
                ],
            ],
        ];
        $expected = new ObjectSet(
            new ParameterBag(),
            new ObjectBag([
                'yummy' => StdClassFactory::create([
                    'base' => 'true',
                    'foo' => 'baz',
                ]),
                'yUmmy' => StdClassFactory::create([
                    'base' => 'true',
                    'foo' => 'baz',
                ]),
            ])
        );
        $actual = $this->loader->loadData($data);

        $this->assertEquals($expected, $actual);
    }

    public function testMultipleInheritanceInTemplates()
    {
        $data = [
            \stdClass::class => [
                'dummy_minimal (template)' => [
                    'foo' => 'bar',
                ],
                'favorite_dummy (template)' => [
                    'foo' => 'baz',
                    'name' => 'favorite',
                    'favoriteNumber' => 2,
                ],
                'dummy_full (template, extends dummy_minimal, extends favorite_dummy)' => [
                    'name' => 'full',
                    'friends' => 'none',
                ],
                'dummy (extends dummy_full)' => [
                    'friends' => 'plenty',
                ],
            ],
        ];
        $expected = new ObjectSet(
            new ParameterBag(),
            new ObjectBag([
                'dummy' => StdClassFactory::create([
                    'foo' => 'baz',
                    'name' => 'full',
                    'favoriteNumber' => 2,
                    'friends' => 'plenty',
                ]),
            ])
        );
        $actual = $this->loader->loadData($data);

        $this->assertEquals($expected, $actual);
    }

    public function testMultipleInheritanceInInstance()
    {
        $data = [
            \stdClass::class => [
                'dummy1 (template)' => [
                    'number' => '1',
                ],
                'dummy2 (template)' => [
                    'number' => 2,
                ],
                'dummy3 (template)' => [
                    'number' => 3,
                ],
                'dummy (extends dummy1, extends dummy2, extends dummy3)' => [
                    'number' => 3,
                ],
            ],
        ];
        $expected = new ObjectSet(
            new ParameterBag(),
            new ObjectBag([
                'dummy' => StdClassFactory::create([
                    'number' => 3,
                ]),
            ])
        );
        $actual = $this->loader->loadData($data);

        $this->assertEquals($expected, $actual);
    }

    public function testUniqueValueGeneration()
    {
        $data = [
            \stdClass::class => [
                'dummy{1..10}' => [
                    'number (unique)' => '<numberBetween(1, 10)>',
                ],
            ],
        ];

        $result = $this->loader->loadData($data);

        $this->assertEquals(0, count($result->getParameters()));
        $this->assertEquals(10, count($result->getObjects()));

        $objects = $result->getObjects();
        $value = [];
        foreach ($objects as $object) {
            $this->assertTrue(1 <= $object->number);
            $this->assertTrue(10 >= $object->number);
            $value[$object->number] = true;
        }

        $this->assertCount(10, $value);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException
     * @expectedExceptionMessageRegExp /^Could not generate a unique value after 150 attempts for ".*"\.$/
     */
    public function testUniqueValueGenerationFailure()
    {
        $data = [
            \stdClass::class => [
                'dummy{1..10}' => [
                    'number (unique)' => '<numberBetween(1, 2)>',
                ],
            ],
        ];

        $this->loader->loadData($data);
    }

    public function testUniqueValueGenerationInAFunctionCall()
    {
        $data = [
            DummyWithRequiredParameterInConstructor::class => [
                'dummy{1..10}' => [
                    '__construct' => [
                        '0 (unique)' => '<numberBetween(1, 10)>',
                    ],
                ],
            ],
        ];

        $result = $this->loader->loadData($data);

        $this->assertEquals(0, count($result->getParameters()));
        $this->assertEquals(10, count($result->getObjects()));

        $objects = $result->getObjects();
        $value = [];
        foreach ($objects as $object) {
            $this->assertTrue(1 <= $object->requiredParam);
            $this->assertTrue(10 >= $object->requiredParam);
            $value[$object->requiredParam] = true;
        }

        $this->assertCount(10, $value);

        try {
            $this->loader->loadData([
                DummyWithRequiredParameterInConstructor::class => [
                    'dummy{1..10}' => [
                        '__construct' => [
                            '0 (unique)' => '<numberBetween(1, 2)>',
                        ],
                    ],
                ],
            ]);
            $this->fail('Expected exception to be thrown.');
        } catch (UniqueValueGenerationLimitReachedException $exception) {
            // Expected result
        }
    }

    public function testUniqueValueGenerationWithDynamicArray()
    {
        $data = [
            \stdClass::class => [
                'related_dummy{1..2}' => [
                    'name' => '<current()>',
                ],
                'dummy' => [
                    'relatedDummies (unique)' => '2x @related_dummy*',
                ],
            ],
        ];

        $result = $this->loader->loadData($data);

        $this->assertEquals(0, count($result->getParameters()));
        $this->assertEquals(3, count($result->getObjects()));

        $dummy = $result->getObjects()['dummy'];
        $this->assertCount(2, $dummy->relatedDummies);
        foreach ($dummy->relatedDummies as $relatedDummy) {
            $this->assertEquals($relatedDummy, $result->getObjects()['related_dummy'.$relatedDummy->name]);
        }
        $this->assertNotEquals($dummy->relatedDummies[0], $dummy->relatedDummies[1]);
        
        try {
            $this->loader->loadData([
                \stdClass::class => [
                    'related_dummy' => [
                        'name' => 'unique',
                    ],
                    'dummy' => [
                        'relatedDummies (unique)' => '2x @related_dummy*',
                    ],
                ],
            ]);
            $this->fail('Expected exception to be thrown.');
        } catch (UniqueValueGenerationLimitReachedException $exception) {
            // expected result
        }
    }

    public function testUniqueValuesAreUniqueAcrossAClass()
    {
        $data = [
            \stdClass::class => [
                'dummy{1..5}' => [
                    'val (unique)' => '<numberBetween(1, 10)>',
                ],
                'dummy{6..10}' => [
                    'val (unique)' => '<numberBetween(1, 10)>',
                ],
            ],
            DummyWithPublicProperty::class => [
                'dummy_with_public_property{1..10}' => [
                    'val (unique)' => '<numberBetween(1, 10)>',
                ],
            ],
        ];

        $result = $this->loader->loadData($data);

        $this->assertEquals(0, count($result->getParameters()));
        $this->assertEquals(20, count($result->getObjects()));

        $objects = $result->getObjects();
        $value = [
            \stdClass::class => [],
            DummyWithPublicProperty::class => [],
        ];
        foreach ($objects as $object) {
            $this->assertTrue(1 <= $object->val);
            $this->assertTrue(10 >= $object->val);
            $value[get_class($object)][$object->val] = true;
        }

        $this->assertCount(10, $value[\stdClass::class]);
        $this->assertCount(10, $value[DummyWithPublicProperty::class]);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureNotFoundException
     * @expectedExceptionMessage Could not find the fixture "unknown".
     */
    public function testThrowsAnExceptionIfInheritFromAnNonExistingFixture()
    {
        $data = [
            \stdClass::class => [
                'dummy (extends unknown)' => [],
            ],
        ];
        $this->loader->loadData($data);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Fixture "dummy" extends "another_dummy" but "another_dummy" is not a template.
     */
    public function testThrowsAnExceptionIfInheritFromAnInexistingTemplate()
    {
        $data = [
            \stdClass::class => [
                'another_dummy' => [],
                'dummy (extends another_dummy)' => [],
            ],
        ];
        $this->loader->loadData($data);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\NoValueForCurrentException
     * @expectedExceptionMessage No value for '<current()>' found for the fixture "dummy".
     */
    public function testThrowsAnExceptionIfUsingCurrentOutOfACollection()
    {
        $data = [
            \stdClass::class => [
                'dummy' => [
                    'foo' => '<current()>',
                ],
            ],
        ];
        $this->loader->loadData($data);
    }

    public function testInjectedParametersAndObjectsAreOverriddenByLocalParameters()
    {
        $this->markTestSkipped('TODO');
        $set = $this->loader->loadData(
            [
                'parameters' => [
                    'foo' => 'baz',
                ],
                \stdClass::class => [
                    'dummy' => [
                        'injected' => false,
                    ],
                ],
            ],
            [
                'foo' => 'bar',
            ],
            [
                'dummy' => StdClassFactory::create([
                    'injected' => true,
                ])
            ]
        );

        $expected = new ObjectSet(
            new ParameterBag([
                'foo' => 'baz',
            ]),
            new ObjectBag([
                'dummy' => StdClassFactory::create([
                    'injected' => false,
                ])
            ])
        );

        $this->assertEquals($expected, $set);
    }

    public function provideFixturesToInstantiate()
    {
        yield 'with default constructor – use default constructor' => [
            [
                DummyWithDefaultConstructor::class => [
                    'dummy' => [],
                ],
            ],
            new DummyWithDefaultConstructor(),
        ];

        yield 'with explicit default constructor - use constructor' => [
            [
                DummyWithExplicitDefaultConstructor::class => [
                    'dummy' => [],
                ],
            ],
            new DummyWithExplicitDefaultConstructor(),
        ];

        yield 'with named constructor - use factory function' => [
            [
                DummyWithNamedConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            DummyWithNamedConstructor::namedConstruct(),
        ];

        yield 'with default constructor and optional parameters without parameters - use constructor function' => [
            [
                DummyWithOptionalParameterInConstructor::class => [
                    'dummy' => [],
                ],
            ],
            new DummyWithOptionalParameterInConstructor(),
        ];

        yield 'with default constructor and optional parameters without parameters - use constructor function' => [
            [
                DummyWithOptionalParameterInConstructor::class => [
                    'dummy' => [],
                ],
            ],
            new DummyWithOptionalParameterInConstructor(),
        ];

        yield 'with default constructor and optional parameters with parameters - use constructor function' => [
            [
                DummyWithOptionalParameterInConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            100
                        ],
                    ],
                ],
            ],
            new DummyWithOptionalParameterInConstructor(100),
        ];

        yield 'with default constructor and required parameters with no parameters - throw exception' => [
            [
                DummyWithRequiredParameterInConstructor::class => [
                    'dummy' => [],
                ],
            ],
            null,
        ];

        yield 'with default constructor and required parameters with parameters - use constructor function' => [
            [
                DummyWithRequiredParameterInConstructor::class => [
                    'dummy' => [
                        '__construct' => [100],
                    ],
                ],
            ],
            new DummyWithRequiredParameterInConstructor(100),
        ];

        yield 'with default constructor and required parameters with parameters and unique value - use constructor function' => [
            [
                DummyWithRequiredParameterInConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            '0 (unique)' => 100,
                        ],
                    ],
                ],
            ],
            new DummyWithRequiredParameterInConstructor(100),
        ];

        yield 'with named constructor and optional parameters with no parameters - use factory function' => [
            [
                DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            DummyWithNamedConstructorAndOptionalParameters::namedConstruct(),
        ];

        yield 'with named constructor and optional parameters with parameters - use factory function' => [
            [
                DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                100,
                            ],
                        ],
                    ],
                ],
            ],
            DummyWithNamedConstructorAndOptionalParameters::namedConstruct(100),
        ];

        yield 'with named constructor and optional parameters with parameters and unique value - use factory function' => [
            [
                DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                '0 (unique)' => 100,
                            ],
                        ],
                    ],
                ],
            ],
            DummyWithNamedConstructorAndOptionalParameters::namedConstruct(100),
        ];

        yield 'with named constructor and required parameters with no parameters - throw exception' => [
            [
                DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            null,
        ];

        yield 'with named constructor and required parameters with parameters - use factory function' => [
            [
                DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                100,
                            ],
                        ],
                    ],
                ],
            ],
            DummyWithNamedConstructorAndRequiredParameters::namedConstruct(100),
        ];

        yield 'with unknown named constructor' => [
            [
                DummyWithDefaultConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            'unknown' => [],
                        ],
                    ],
                ],
            ],
            null,
        ];

        yield 'with private constructor – throw exception' => [
            [
                DummyWithPrivateConstructor::class => [
                    'dummy' => [],
                ],
            ],
            null,
        ];

        yield 'with protected constructor – throw exception' => [
            [
                DummyWithProtectedConstructor::class => [
                    'dummy' => [],
                ],
            ],
            null,
        ];

        yield 'with private named constructor – throw exception' => [
            [
                DummyWithNamedPrivateConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            null,
        ];

        yield 'with default constructor but specified no constructor – use reflection' => [
            [
                DummyWithDefaultConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new \ReflectionClass(DummyWithDefaultConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with explicit constructor but specified no constructor – use reflection' => [
            [
                DummyWithExplicitDefaultConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new \ReflectionClass(DummyWithExplicitDefaultConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with named constructor but specified no constructor – use reflection' => [
            [
                DummyWithNamedConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new \ReflectionClass(DummyWithNamedConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with named constructor and optional parameters but specified no constructor – use reflection' => [
            [
                DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new \ReflectionClass(DummyWithNamedConstructorAndOptionalParameters::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with named constructor and required parameters but specified no constructor – use reflection' => [
            [
                DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new \ReflectionClass(DummyWithNamedConstructorAndRequiredParameters::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with optional parameters in constructor but specified no constructor – use reflection' => [
            [
                DummyWithOptionalParameterInConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new \ReflectionClass(DummyWithOptionalParameterInConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with required parameters in constructor but specified no constructor – use reflection' => [
            [
                DummyWithRequiredParameterInConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new \ReflectionClass(DummyWithRequiredParameterInConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with private constructor – use reflection' => [
            [
                DummyWithPrivateConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new \ReflectionClass(DummyWithPrivateConstructor::class))->newInstanceWithoutConstructor(),

        ];

        yield 'with protected constructor – use reflection' => [
            [
                DummyWithProtectedConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new \ReflectionClass(DummyWithProtectedConstructor::class))->newInstanceWithoutConstructor(),

        ];

        yield 'with private named constructor – use reflection' => [
            [
                DummyWithNamedPrivateConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new \ReflectionClass(DummyWithNamedPrivateConstructor::class))->newInstanceWithoutConstructor(),
        ];
    }

    public function provideFixturesToHydrate()
    {
        yield 'public camelCase property' => [
            [
                CamelCaseDummy::class => [
                    'dummy' => [
                        'publicProperty' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (function (CamelCaseDummy $dummy) {
                    $dummy->publicProperty = 'bob';

                    return $dummy;
                })(new CamelCaseDummy())
            ],
        ];

        yield 'public snake_case property' => [
            [
                SnakeCaseDummy::class => [
                    'dummy' => [
                        'public_property' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (function (SnakeCaseDummy $dummy) {
                    $dummy->public_property = 'bob';

                    return $dummy;
                })(new SnakeCaseDummy())
            ],
        ];

        yield 'public PascalCase property' => [
            [
                PascalCaseDummy::class => [
                    'dummy' => [
                        'PublicProperty' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (function (PascalCaseDummy $dummy) {
                    $dummy->PublicProperty = 'bob';

                    return $dummy;
                })(new PascalCaseDummy())
            ],
        ];

        yield 'public setter camelCase property' => [
            [
                CamelCaseDummy::class => [
                    'dummy' => [
                        'setterProperty' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (function (CamelCaseDummy $dummy) {
                    $dummy->setSetterProperty('bob');

                    return $dummy;
                })(new CamelCaseDummy())
            ],
        ];

        yield 'public setter snake_case property' => [
            [
                SnakeCaseDummy::class => [
                    'dummy' => [
                        'setter_property' => 'bob',
                    ],
                ],
            ],
            null,
        ];

        yield 'magic call camelCase property' => [
            [
                MagicCallDummy::class => [
                    'dummy' => [
                        'magicProperty' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (function (MagicCallDummy $dummy) {
                    $dummy->setMagicProperty('bob');

                    return $dummy;
                })(new MagicCallDummy())
            ],
        ];

        yield 'magic call snake_case property' => [
            [
                MagicCallDummy::class => [
                    'dummy' => [
                        'magic_property' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (function (MagicCallDummy $dummy) {
                    $dummy->setMagicProperty('bob');

                    return $dummy;
                })(new MagicCallDummy())
            ],
        ];

        yield 'magic call PascalCase property' => [
            [
                MagicCallDummy::class => [
                    'dummy' => [
                        'MagicProperty' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (function (MagicCallDummy $dummy) {
                    $dummy->setMagicProperty('bob');

                    return $dummy;
                })(new MagicCallDummy())
            ],
        ];
    }

    public function provideFixturesToGenerate()
    {
        yield 'empty instance' => [
            [
                \stdClass::class => [
                    'dummy' => [],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => new \stdClass(),
                ],
            ],
        ];

        yield 'static value' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ])
                ],
            ],
        ];

        yield 'reference value' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'dummy' => '@dummy',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => $dummy = StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'dummy' => $dummy,
                    ]),
                ],
            ],
        ];

        yield 'inverted reference value' => [
            [
                \stdClass::class => [
                    'another_dummy' => [
                        'dummy' => '@dummy',
                    ],
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => $dummy = StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'dummy' => $dummy,
                    ]),
                ],
            ],
        ];

        yield 'property reference value' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'foo' => '@dummy->foo',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => $dummy = StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                ],
            ],
        ];

        yield 'non existing property reference' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'foo' => '@dummy->bob',
                    ],
                ],
            ],
            null,
        ];

        yield 'property reference value with a getter' => [
            [
                DummyWithGetter::class => [
                    'dummy' => [
                        'name' => 'foo',
                    ],
                    'another_dummy' => [
                        'name' => '@dummy->name',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => $dummy = (new DummyWithGetter())->setName('foo'),
                    'another_dummy' => (new DummyWithGetter())->setName('__get__foo'),
                ],
            ]
        ];

        yield 'wildcard reference value' => [
            [
                \stdClass::class => [
                    'dummy_0' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'dummy' => '@dummy*',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy_0' => $dummy = StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'dummy' => $dummy,
                    ]),
                ],
            ],
        ];

        yield 'wildcard property reference value' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'foo' => '@dummy*->foo',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                ],
            ],
        ];

        yield 'dynamic array value' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'dummies' => '3x @dummy',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => $dummy = StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'dummies' => [$dummy, $dummy, $dummy]
                    ]),
                ],
            ],
        ];

        yield 'dynamic array value with wildcard' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'dummies' => '3x @dummy*',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => $dummy = StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'dummies' => [$dummy, $dummy, $dummy]
                    ]),
                ],
            ],
        ];

        yield 'objects with dots in their references' => [
            [
                \stdClass::class => [
                    'user.alice' => [
                        'username' => 'alice',
                    ],
                    'user.alias.alice_alias' => [
                        'username' => '@user.alice->username',
                    ],
                    'user.deep_alias' => [
                        'username' => '@user.alias.alice_alias->username',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'user.alice' => StdClassFactory::create([
                        'username' => 'alice',
                    ]),
                    'user.alias.alice_alias' => StdClassFactory::create([
                        'username' => 'alice',
                    ]),
                    'user.deep_alias' => StdClassFactory::create([
                        'username' => 'alice',
                    ]),
                ],
            ],
        ];

        yield '[special characters] references with underscores' => [
            [
                \stdClass::class => [
                    'user_alice' => [
                        'username' => 'alice',
                    ],
                    'user_alias' => [
                        'username' => '@user_alice->username',
                    ],
                    'user_deep_alias' => [
                        'username' => '@user_alias->username',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'user_alice' => StdClassFactory::create([
                        'username' => 'alice',
                    ]),
                    'user_alias' => StdClassFactory::create([
                        'username' => 'alice',
                    ]),
                    'user_deep_alias' => StdClassFactory::create([
                        'username' => 'alice',
                    ]),
                ],
            ],
        ];

        yield '[special characters] references with slashes' => [
            [
                \stdClass::class => [
                    'user/alice' => [
                        'username' => 'alice',
                    ],
                    'user/alias/alice_alias' => [
                        'username' => '@user/alice->username',
                    ],
                    'user/deep_alias' => [
                        'username' => '@user/alias/alice_alias->username',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'user/alice' => StdClassFactory::create([
                        'username' => 'alice',
                    ]),
                    'user/alias/alice_alias' => StdClassFactory::create([
                        'username' => 'alice',
                    ]),
                    'user/deep_alias' => StdClassFactory::create([
                        'username' => 'alice',
                    ]),
                ],
            ],
        ];

        yield '[provider] faker functions' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => '<shuffle([1])>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => [1],
                    ]),
                ],
            ],
        ];

        yield '[self reference] alone' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'itself' => '@self',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (function() {
                        $dummy = new \stdClass();
                        $dummy->itself = $dummy;

                        return $dummy;
                    })(),
                ],
            ],
        ];

// TODO
//        yield '[self reference] alone' => [
//            [
//                \stdClass::class => [
//                    'dummy' => [
//                        'itself' => '@<("self")>',
//                    ],
//                ],
//            ],
//            [
//                'parameters' => [],
//                'objects' => [
//                    'dummy' => (function() {
//                        $dummy = new \stdClass();
//                        $dummy->itself = $dummy;
//
//                        return $dummy;
//                    })(),
//                ],
//            ],
//        ];

        yield '[self reference] property' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                        'itself' => '@self',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (function() {
                        $dummy = new \stdClass();
                        $dummy->foo = 'bar';
                        $dummy->itself = $dummy;

                        return $dummy;
                    })(),
                ],
            ],
        ];

        yield 'identity provider' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                        'identity_foo' => '<identity($foo)>',
                    ],
                    'another_dummy' => [
                        'foo' => 'bar_baz',
                        'identity_foo' => '<identity(str_replace("_", " ", $foo))>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                        'identity_foo' => 'bar',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'foo' => 'bar_baz',
                        'identity_foo' => 'bar baz',
                    ]),
                ],
            ],
        ];

        yield '[self reference] alone' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'itself' => '@self',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (function() {
                        $dummy = new \stdClass();
                        $dummy->itself = $dummy;

                        return $dummy;
                    })(),
                ],
            ],
        ];

// TODO
//        yield '[self reference] alone' => [
//            [
//                \stdClass::class => [
//                    'dummy' => [
//                        'itself' => '@<("self")>',
//                    ],
//                ],
//            ],
//            [
//                'parameters' => [],
//                'objects' => [
//                    'dummy' => (function() {
//                        $dummy = new \stdClass();
//                        $dummy->itself = $dummy;
//
//                        return $dummy;
//                    })(),
//                ],
//            ],
//        ];

        yield '[self reference] property' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                        'itself' => '@self',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (function() {
                        $dummy = new \stdClass();
                        $dummy->foo = 'bar';
                        $dummy->itself = $dummy;

                        return $dummy;
                    })(),
                ],
            ],
        ];

        yield '[variable] nominal' => [
            [
                \Nelmio\Alice\Entity\DummyWithGetter::class => [
                    'dummy' => [
                        'foo' => 'bar',
                        'fooVal' => '$foo',
                    ],
                    'another_dummy' => [
                        'foo' => 'bar',
                        'fooVal' => '@self->foo',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (function (\Nelmio\Alice\Entity\DummyWithGetter $dummy) {
                        $dummy->setFoo('bar');
                        $dummy->fooVal = 'bar';

                        return $dummy;
                    })(new \Nelmio\Alice\Entity\DummyWithGetter()),
                    'another_dummy' => (function (\Nelmio\Alice\Entity\DummyWithGetter $dummy) {
                        $dummy->setFoo('bar');
                        $dummy->fooVal = 'rab';

                        return $dummy;
                    })(new \Nelmio\Alice\Entity\DummyWithGetter()),
                ],
            ],
        ];

        yield '[variable] variables are scoped to the fixture' => [
            [
                \Nelmio\Alice\Entity\DummyWithGetter::class => [
                    'dummy' => [
                        'foo' => 'bar',
                        'fooVal' => '$foo',
                    ],
                    'another_dummy' => [
                        'foo' => '$foo',
                    ],
                ],
            ],
            null,
        ];

        yield '[identity] evaluate the argument as if it was a plain PHP function' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => '<("Hello"." "."world!")>',
                        'bar' => '<(str_replace("_", " ", "Hello_world!"))>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'Hello world!',
                        'bar' => 'Hello world!',
                    ]),
                ],
            ],
        ];

        yield '[identity] has access to variables' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                        'foz' => '<($foo)>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                        'foz' => 'bar',
                    ]),
                ],
            ],
        ];

        yield '[identity] has access to variables' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'foo' => '<(@dummy->foo)>'
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                ],
            ],
        ];

        yield '[templating] templates are not returned' => [
            [
                \stdClass::class => [
                    'base_dummy (template)' => [],
                    'dummy' => [],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => new \stdClass(),
                ],
            ],
        ];

        //TODO: check
//        yield '[templating] cannot extend a non template fixture' => [
//            [
//                \stdClass::class => [
//                    'base_dummy' => [],
//                    'dummy (extends base_dummy)' => [],
//                ],
//            ],
//            null,
//        ];
//
        yield '[templating] nominal' => [
            [
                \stdClass::class => [
                    'base_dummy (template)' => [
                        'foo' => 'bar',
                    ],
                    'another_base_dummy (template)' => [
                        'foo' => 'baz',
                        'ping' => 'pong',
                    ],
                    'dummy0 (extends base_dummy, extends another_base_dummy)' => [
                        'foo' => 'baz',
                        'ping' => 'pong',
                    ],
                    'dummy1 (extends another_base_dummy, extends base_dummy)' => [
                        'foo' => 'bar',
                        'ping' => 'pong',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy0' => StdClassFactory::create([
                        'foo' => 'baz',
                        'ping' => 'pong',
                    ]),
                    'dummy1' => StdClassFactory::create([
                        'foo' => 'bar',
                        'ping' => 'pong',
                    ]),
                ],
            ],
        ];

        yield '[current] nominal' => [
            [
                \stdClass::class => [
                    'dummy{1..2}' => [
                        'val' => '<current()>',
                    ],
                    'dummy_{alice, bob}' => [
                        'val' => '<current()>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy1' => StdClassFactory::create([
                        'val' => 1,
                    ]),
                    'dummy2' => StdClassFactory::create([
                        'val' => 2,
                    ]),
                    'dummy_alice' => StdClassFactory::create([
                        'val' => 'alice',
                    ]),
                    'dummy_bob' => StdClassFactory::create([
                        'val' => 'bob',
                    ]),
                ],
            ],
        ];

        yield '[current] in constructor' => [
            [
                DummyWithConstructorParam::class => [
                    'dummy{1..2}' => [
                        '__construct' => ['<current()>'],
                    ],
                    'dummy_{alice, bob}' => [
                        '__construct' => ['<current()>'],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy1' => new DummyWithConstructorParam(1),
                    'dummy2' => new DummyWithConstructorParam(2),
                    'dummy_alice' => new DummyWithConstructorParam('alice'),
                    'dummy_bob' => new DummyWithConstructorParam('bob'),
                ],
            ],
        ];

        //TODO: make this pass
//        yield 'at literal is not resolved' => [
//            [
//                \stdClass::class => [
//                    'dummy' => [
//                        'atValues' => [
//                            '\\@<("hello")>',
//                            '\\\\@foo',
//                            '\\\\\\@foo',
//                            '\\foo',
//                            '\\\\foo',
//                            '\\\\\\foo',
//                        ],
//                    ],
//                ],
//            ],
//            [
//                'parameters' => [],
//                'objects' => [
//                    'dummy' => StdClassFactory::create([
//                        'atValues' => [
//                            '@hello',
//                            '\\@foo',
//                            '\\\\@foo',
//                            '\\foo',
//                            '\\\\foo',
//                            '\\\\\\foo',
//                        ]
//                    ]),
//                ],
//            ],
//        ];

        yield '[parameter] simple' => [
            [
                'parameters' => [
                    'foo' => 'bar',
                ],
                \stdClass::class => [
                    'dummy' => [
                        'foo' => '<{foo}>',
                    ],
                ],
            ],
            [
                'parameters' => [
                    'foo' => 'bar',
                ],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                ],
            ],
        ];

        yield '[parameter] array parameter' => [
            [
                'parameters' => [
                    'foo' => ['bar'],
                ],
                \stdClass::class => [
                    'dummy' => [
                        'foo' => '<randomElement(<{foo}>)>',
                    ],
                ],
            ],
            [
                'parameters' => [
                    'foo' => ['bar'],
                ],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                ],
            ],
        ];

        yield '[parameter] composite parameter' => [
            [
                'parameters' => [
                    'foo' => 'NaN',
                    'bar' => 'Bat',
                    'composite' => '<{foo}> <{bar}>!',
                ],
                \stdClass::class => [
                    'dummy' => [
                        'foo' => '<{foo}> <{bar}>!',
                    ],
                    'another_dummy' => [
                        'foo' => '<{composite}>',
                    ],
                ],
            ],
            [
                'parameters' => [
                    'foo' => 'NaN',
                    'bar' => 'Bat',
                    'composite' => 'NaN Bat!',
                ],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'NaN Bat!',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'foo' => 'NaN Bat!',
                    ]),
                ],
            ],
        ];

        //TODO; see LoaderTest::testDynamicParametersLoading in v2
//        yield '[parameter] dynamic parameter' => [
//            [
//                'parameters' => [
//                    'username_alice' => 'Alice',
//                    'username_bob' => 'Bob',
//                ],
//                \stdClass::class => [
//                    'user_{alice, bob}' => [
//                        'username' => '<{username_<current()>}>',
//                    ],
//                ],
//            ],
//            [
//                'parameters' => [
//                    'username_alice' => 'Alice',
//                    'username_bob' => 'Bob',
//                ],
//                'objects' => [
//                    'user_alice' => StdClassFactory::create([
//                        'username' => 'Alice',
//                    ]),
//                    'another_dummy' => StdClassFactory::create([
//                        'username' => 'Bob',
//                    ]),
//                ],
//            ],
//        ];

        yield '[parameter] nested parameter' => [
            [
                'parameters' => [
                    'param1' => 2,
                    'param2' => 1,
                    'param3' => '<{param<{param2}>}>',
                ],
                'objects' => [],
            ],
            [
                'parameters' => [
                    'param1' => 2,
                    'param2' => 1,
                    'param3' => '2',
                ],
                'objects' => [],
            ],
        ];

        yield 'dynamic array with scalar value' => [
            [
                \stdClass::class => [
                    'dummy' => [
                        'foo' => '5x bar',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => ['bar', 'bar', 'bar', 'bar', 'bar']
                    ]),
                ],
            ],
        ];
    }

    //TODO: test with circular reference
}
