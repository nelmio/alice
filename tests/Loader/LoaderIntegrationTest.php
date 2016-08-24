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
use Nelmio\Alice\Entity\DummyWithDate;
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
            return;
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
            return;
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
            return;
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
                        100,
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
                    $dummy->magicProperty('bob');

                    return $dummy;
                })(new MagicCallDummy())
            ],
        ];

        yield 'magic call snake_case property' => [
            [
                SnakeCaseDummy::class => [
                    'dummy' => [
                        'magic_property' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (function (MagicCallDummy $dummy) {
                    $dummy->magic_property('bob');

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
                    $dummy->MagicProperty('bob');

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
                    'another_dummy' => StdClassFactory::create([
                        'dummy' => $dummy,
                    ]),
                    'dummy' => $dummy = StdClassFactory::create([
                        'foo' => 'bar',
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
    }
}
