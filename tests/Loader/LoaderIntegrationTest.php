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
class LoaderIntegrationTest extends \PHPUnit_Framework_TestCase
{
    const FILES_DIR = __DIR__.'/../../fixtures/Parser/files';

    /**
     * @var FileLoaderInterface|DataLoaderInterface
     */
    private $loader;

    public function setUp()
    {
        $this->loader = new IsolatedLoader();
    }

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
        $this->loader->loadFile(self::FILES_DIR.'/unsupported/plain_file');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^The file ".*?no_return.php" must return a PHP array\.$/
     */
    public function testLoadPhpFileWhichDoesNotReturnAnything()
    {
        $this->loader->loadFile(self::FILES_DIR.'/php/no_return.php');
    }

    public function testLoadEmptyData()
    {
        $set = $this->loader->loadData([]);

        $this->assertEquals(
            new ObjectSet(new ParameterBag(), new ObjectBag()),
            $set
        );
    }

    public function testLoadEmptyInstances()
    {
        $set = $this->loader->loadData([
            \stdClass::class => [
                'alice' => [],
                'bob' => [],
            ],
        ]);
        $objects = $set->getObjects();

        $this->assertCount(0, $set->getParameters());
        $this->assertCount(2, $objects);

        $this->assertInstanceOf(\stdClass::class, $objects['alice']);
        $this->assertInstanceOf(\stdClass::class, $objects['bob']);
    }

    public function testLoadASequencedOfItems()
    {
        $set = $this->loader->loadData([
            \stdClass::class => [
                'dummy{1..10}' => [],
            ],
        ]);
        $objects = $set->getObjects();

        $this->assertCount(0, $set->getParameters());
        $this->assertCount(10, $objects);

        for ($i = 1; $i <= 10; $i++) {
            $this->assertInstanceOf(\stdClass::class, $objects['dummy'.$i]);
            // TODO: test value for current
        }
    }

    public function testLoadAListOfItems()
    {
        $set = $this->loader->loadData([
            \stdClass::class => [
                'user_{alice, bob, fred}' => [],
            ],
        ]);
        $objects = $set->getObjects();

        $this->assertCount(0, $set->getParameters());
        $this->assertCount(3, $objects);

        $this->assertSame(
            [
                'user_alice',
                'user_bob',
                'user_fred',
            ],
            array_keys($objects)
        );
        // TODO: test value for current
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
    }

    //TODO: test with circular reference
}
