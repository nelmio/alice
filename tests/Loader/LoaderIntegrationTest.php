<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Loader;

use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use LogicException;
use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\Entity as FixtureEntity;
use Nelmio\Alice\Entity\DummyWithConstructorAndCallable;
use Nelmio\Alice\Entity\DummyWithConstructorParam;
use Nelmio\Alice\Entity\StdClassFactory;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\FilesLoaderInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\FixtureNotFoundException;
use Nelmio\Alice\Throwable\Exception\Generator\DebugUnexpectedValueException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationException;
use Nelmio\Alice\Throwable\Exception\NoValueForCurrentException;
use Nelmio\Alice\Throwable\Exception\Parser\ParserNotFoundException;
use Nelmio\Alice\Throwable\GenerationThrowable;
use Nelmio\Alice\User;
use Nelmio\Alice\UserDetail;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionObject;
use stdClass;
use Throwable;
use TypeError;

/**
 * @group integration
 *
 * @coversNothing
 * @internal
 */
class LoaderIntegrationTest extends TestCase
{
    public const PARSER_FILES_DIR = __DIR__.'/../../fixtures/Parser/files';
    public const FIXTURES_FILES_DIR = __DIR__.'/../../fixtures/Integration';

    /**
     * @var FilesLoaderInterface|FileLoaderInterface|DataLoaderInterface
     */
    protected $loader;

    /**
     * @var FilesLoaderInterface|FileLoaderInterface|DataLoaderInterface
     */
    protected $nonIsolatedLoader;

    protected function setUp(): void
    {
        $this->loader = new IsolatedLoader();
        $this->nonIsolatedLoader = new NativeLoader();
    }

    public function testLoadFile(): void
    {
        $objects = $this->loader->loadFile(self::FIXTURES_FILES_DIR.'/dummy.yml')->getObjects();

        self::assertEquals(
            [
                'dummy' => new stdClass(),
            ],
            $objects,
        );
    }

    public function testLoadFiles(): void
    {
        $objects = $this->loader->loadFiles([
            self::FIXTURES_FILES_DIR.'/dummy.yml',
            self::FIXTURES_FILES_DIR.'/another_dummy.yml',
        ])->getObjects();

        self::assertEquals(
            [
                'dummy' => new stdClass(),
                'another_dummy' => new stdClass(),
            ],
            $objects,
        );
    }

    public function testLoadJsonFile(): void
    {
        $objects = $this->loader->loadFiles([
            self::FIXTURES_FILES_DIR.'/dummy.json',
        ])->getObjects();

        self::assertEquals(
            [
                'dummy1' => new stdClass(),
                'dummy2' => new stdClass(),
            ],
            $objects,
        );
    }

    public function testLoadRecursiveFiles(): void
    {
        $objects = $this->loader->loadFiles([
            self::FIXTURES_FILES_DIR.'/recursive_0/dummy.yml',
        ])->getObjects();

        self::assertEquals(
            [
                'dummy' => new stdClass(),
                'another_dummy' => new stdClass(),
            ],
            $objects,
        );

        $objects = $this->loader->loadFiles([
            self::FIXTURES_FILES_DIR.'/recursive_1/dummy.yml',
        ])->getObjects();

        self::assertEquals(
            [
                'dummy' => new stdClass(),
                'another_dummy' => new stdClass(),
                'yet_another_dummy' => new stdClass(),
            ],
            $objects,
        );

        $objects = $this->loader->loadFiles([
            self::FIXTURES_FILES_DIR.'/recursive_1/another_dummy.yml',
        ])->getObjects();

        self::assertEquals(
            [
                'dummy' => new stdClass(),
                'another_dummy' => new stdClass(),
                'yet_another_dummy' => new stdClass(),
            ],
            $objects,
        );
    }

    public function testLoadInexistingFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The file "unknown.yml" could not be found.');

        $this->loader->loadFile('unknown.yml');
    }

    public function testLoadUnsupportedFileFormat(): void
    {
        $this->expectException(ParserNotFoundException::class);
        $this->expectExceptionMessageMatches('/^No suitable parser found for the file ".*?plain_file"\.$/');

        $this->loader->loadFile(self::PARSER_FILES_DIR.'/unsupported/plain_file');
    }

    public function testLoadPhpFileWhichDoesNotReturnAnything(): void
    {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessageMatches('/^The file ".*?no_return.php" must return a PHP array\.$/');

        $this->loader->loadFile(self::PARSER_FILES_DIR.'/php/no_return.php');
    }

    public function testLoadEmptyData(): void
    {
        $set = $this->loader->loadData([]);

        self::assertEquals(
            new ObjectSet(new ParameterBag(), new ObjectBag()),
            $set,
        );
    }

    /**
     * @dataProvider provideFixturesToInstantiate
     *
     * @param object|string $expected
     */
    public function testObjectInstantiation(array $data, $expected, ?string $instanceof = null): void
    {
        try {
            $objects = $this->loader->loadData($data)->getObjects();

            if (is_string($expected)) {
                self::fail('Expected exception to be thrown.');
            }
        } catch (Throwable $throwable) {
            if (is_string($expected)) {
                self::assertNotNull($instanceof, 'Expected to know the type of the throwable expected.');

                self::assertInstanceOf($instanceof, $throwable);

                self::assertSame($expected, $throwable->getMessage());

                return;
            }

            throw $throwable;
        }

        self::assertCount(1, $objects);
        self::assertEquals($expected, $objects['dummy']);
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Using factories with the fixture keyword "__construct" has been deprecated since 3.0.0 and will no longer be supported in Alice 4.0.0. Use "__factory" instead.
     *
     * @dataProvider provideLegacyFixturesToInstantiate
     *
     * @param object|string $expected
     */
    public function testObjectInstantiationWithLegacyConstruct(array $data, $expected, ?string $instanceof = null): void
    {
        try {
            $objects = $this->loader->loadData($data)->getObjects();

            if (is_string($expected)) {
                self::fail('Expected exception to be thrown.');
            }
        } catch (Throwable $throwable) {
            if (is_string($expected)) {
                self::assertNotNull($instanceof, 'Expected to know the type of the throwable expected.');

                self::assertInstanceOf($instanceof, $throwable);

                self::assertSame($expected, $throwable->getMessage());

                return;
            }

            throw $throwable;
        }

        self::assertCount(1, $objects);
        self::assertEquals($expected, $objects['dummy']);
    }

    /**
     * @dataProvider provideFixturesToInstantiateWithFactory
     *
     * @param array|string $expected
     */
    public function testObjectInstantiationWithFactory(array $data, $expected, ?string $instanceof = null): void
    {
        try {
            $objects = $this->loader->loadData($data)->getObjects();

            if (is_string($expected)) {
                self::fail('Expected exception to be thrown.');
            }
        } catch (Throwable $throwable) {
            if (is_string($expected)) {
                self::assertNotNull($instanceof, 'Expected to know the type of the throwable expected.');

                self::assertInstanceOf($instanceof, $throwable);

                self::assertSame($expected, $throwable->getMessage());

                return;
            }

            throw $throwable;
        }

        self::assertCount(1, $objects);
        self::assertEquals($expected, $objects['dummy']);
    }

    public function testCannotUseBothConstructAndFactoryAtTheSameTime(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot use the fixture property "__construct" and "__factory" together.');

        $this->loader->loadData([
            stdClass::class => [
                'dummy' => [
                    '__construct' => [],
                    '__factory' => [],
                ],
            ],
        ]);
    }

    /**
     * @dataProvider provideFixtureToInstantiateWithDeprecatedConstructor
     *
     * @group legacy
     *
     * @expectedDeprecation Using factories with the fixture keyword "__construct" has been deprecated since 3.0.0 and will no longer be supported in Alice 4.0.0. Use "__factory" instead.
     * @param mixed $expected
     */
    public function testUsingConstructorAsAFactoryIsDeprecated(array $data, $expected): void
    {
        $objects = $this->loader->loadData($data)->getObjects();

        self::assertCount(1, $objects);
        self::assertEquals($expected, $objects['dummy']);
    }

    /**
     * @dataProvider provideFixturesToHydrate
     *
     * @param array|string $expected
     */
    public function testObjectHydration(array $data, $expected, ?string $instanceof = null): void
    {
        try {
            $objects = $this->loader->loadData($data)->getObjects();

            if (!is_array($expected)) {
                self::fail('Expected exception to be thrown.');
            }
        } catch (Throwable $throwable) {
            if (is_string($expected)) {
                self::assertNotNull($instanceof, 'Expected to know the type of the throwable expected.');

                self::assertInstanceOf($instanceof, $throwable);

                self::assertSame($expected, $throwable->getMessage());

                return;
            }

            throw $throwable;
        }

        self::assertCount(count($expected), $objects);
        self::assertEquals($expected, $objects);
    }

    /**
     * @dataProvider provideFixturesToGenerate
     *
     * @param string|array $expected
     */
    public function testFixtureGeneration(array $data, $expected, ?string $instanceof = null): void
    {
        try {
            $set = $this->loader->loadData($data);

            if (!is_array($expected)) {
                self::fail('Expected exception to be thrown.');
            }
        } catch (Throwable $exception) {
            if (is_string($expected)) {
                self::assertNotNull($instanceof, 'Expected to know the type of the throwable expected.');

                self::assertInstanceOf($instanceof, $exception);

                self::assertSame($expected, $exception->getMessage());

                return;
            }

            throw $exception;
        }

        $expectedParameters = $expected['parameters'];
        $actualParameters = $set->getParameters();
        self::assertCount(count($expectedParameters), $actualParameters);
        self::assertEquals($expectedParameters, $actualParameters);

        $expectedObjects = $expected['objects'];
        $actualObjects = $set->getObjects();
        self::assertCount(count($expectedObjects), $actualObjects);
        self::assertEquals($expectedObjects, $actualObjects);
    }

    public function testWithReflection(): void
    {
        $loader = new WithReflectionLoader();

        $data = [
            FixtureEntity\DummyWithPrivateProperty::class => [
                'dummy' => [
                    'val' => 'bar',
                ],
            ],
        ];

        $expected = ['dummy' => new FixtureEntity\DummyWithPrivateProperty('bar')];

        $set = $loader->loadData($data);

        $actual = $set->getObjects();
        self::assertCount(count($expected), $actual);
        self::assertEquals($expected, $actual);
    }

    public function testLoadASetOfDataWithInjectedObjects(): void
    {
        $set = $this->loader->loadData(
            [
                stdClass::class => [
                    'dummy' => [
                        'relatedDummy' => '@injected_dummy',
                    ],
                ],
            ],
            [],
            [
                'injected_dummy' => StdClassFactory::create(['injected' => true]),
            ],
        );
        $objects = $set->getObjects();

        self::assertCount(0, $set->getParameters());
        self::assertCount(2, $objects);

        self::assertEquals(
            [
                'injected_dummy' => $injectedDummy = StdClassFactory::create(['injected' => true]),
                'dummy' => StdClassFactory::create(['relatedDummy' => $injectedDummy]),
            ],
            $objects,
        );
    }

    /**
     * @group legacy
     *
     * @expectedDeprecation Using factories with the fixture keyword "__construct" has been deprecated since 3.0.0 and will no longer be supported in Alice 4.0.0. Use "__factory" instead.
     */
    public function testIfAFixtureAndAnInjectedObjectHaveTheSameIdThenTheInjectedObjectIsOverridden(): void
    {
        $set = $this->loader->loadData(
            [
                FixtureEntity\ImmutableStd::class => [
                    'dummy' => [
                        '__construct' => [
                            ['relatedDummy' => '@another_dummy'],
                        ],
                    ],
                ],
                stdClass::class => [
                    'another_dummy' => [
                        '__construct' => [
                            StdClassFactory::class.'::create' => [['injected' => false]],
                        ],
                    ],
                ],
            ],
            [],
            [
                'another_dummy' => StdClassFactory::create(['injected' => true]),
            ],
        );
        $objects = $set->getObjects();

        self::assertCount(0, $set->getParameters());
        self::assertCount(2, $objects);

        self::assertEquals(
            [
                'dummy' => new FixtureEntity\ImmutableStd([
                    'relatedDummy' => StdClassFactory::create(['injected' => false]),
                ]),
                'another_dummy' => StdClassFactory::create(['injected' => false]),
            ],
            $objects,
        );
    }

    public function testLoadOptionalValues(): void
    {
        $data = [
            stdClass::class => [
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

        self::assertCount(0, $set->getParameters());

        $objects = $set->getObjects();
        self::assertCount(4, $objects);

        self::assertContains($objects['user0']->username, ['something', null]);
        self::assertContains($objects['user1']->username, ['something', 'nothing']);
        self::assertEquals('nothing', $objects['user2']->username);
        self::assertEquals('something', $objects['user3']->username);
    }

    public function testLoadTwoSuccessiveFakerFunctions(): void
    {
        $data = [
            stdClass::class => [
                'user' => [
                    'username' => '<firstName()> <lastName()>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        self::assertCount(0, $set->getParameters());

        $objects = $set->getObjects();
        self::assertCount(1, $objects);

        $user = $objects['user'];
        self::assertInstanceOf(stdClass::class, $user);
        self::assertMatchesRegularExpression('/^[\w\']+ [\w\']+$/i', $user->username);
    }

    public function testLoadFakerFunctionWithData(): void
    {
        $data = [
            stdClass::class => [
                'user' => [
                    'age' => '<numberBetween(10, 10)>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        self::assertCount(0, $set->getParameters());

        $objects = $set->getObjects();
        self::assertCount(1, $objects);

        $user = $objects['user'];
        self::assertInstanceOf(stdClass::class, $user);
        self::assertSame(10, $user->age);
    }

    public function testLoadLocalizedFakerFunctionWithData(): void
    {
        $data = [
            stdClass::class => [
                'user' => [
                    'siren' => '<fr_FR:siren()>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        self::assertCount(0, $set->getParameters());

        $objects = $set->getObjects();
        self::assertCount(1, $objects);

        $user = $objects['user'];
        self::assertInstanceOf(stdClass::class, $user);
        self::assertMatchesRegularExpression('/^\d{3} \d{3} \d{3}$/', $user->siren);
    }

    public function testLoadFakerFunctionWithPhpArguments(): void
    {
        $data = [
            stdClass::class => [
                'user' => [
                    'updatedAt' => '<dateTimeBetween(<("yest"."erday")>, <(strrev("omot")."rrow")>)>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        self::assertCount(0, $set->getParameters());

        $objects = $set->getObjects();
        self::assertCount(1, $objects);

        $user = $objects['user'];
        self::assertInstanceOf(stdClass::class, $user);

        $updatedAt = $user->updatedAt;
        self::assertInstanceOf(DateTimeInterface::class, $updatedAt);
        /** @var DateTimeInterface $updatedAt */
        self::assertGreaterThanOrEqual(strtotime('yesterday'), $updatedAt->getTimestamp());
        self::assertLessThanOrEqual(strtotime('tomorrow'), $updatedAt->getTimestamp());
    }

    public function testLoadSelfReferencedFixture(): void
    {
        $data = [
            stdClass::class => [
                'dummy' => [
                    'relatedDummy' => '@dummy*',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        self::assertCount(0, $set->getParameters());

        $objects = $set->getObjects();
        self::assertCount(1, $objects);

        $expectedDummy = new stdClass();
        $expectedDummy->relatedDummy = $expectedDummy;

        self::assertEquals($expectedDummy, $objects['dummy']);
    }

    public function testLoadAutomaticallyEscapedReference(): void
    {
        $data = [
            stdClass::class => [
                'dummy' => [
                    'email' => 'email@example.com',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        self::assertCount(0, $set->getParameters());

        $objects = $set->getObjects();
        self::assertCount(1, $objects);

        $expectedDummy = StdClassFactory::create([
            'email' => 'email@example.com',
        ]);

        self::assertEquals($expectedDummy, $objects['dummy']);
    }

    public function testLoadSelfReferencedFixtures(): void
    {
        $data = [
            stdClass::class => [
                'dummy{1..2}' => [
                    'relatedDummies' => '3x @dummy*',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        self::assertCount(0, $set->getParameters());

        $objects = $set->getObjects();
        self::assertCount(2, $objects);
    }

    public function testLoadRangeWithStepFixtures(): void
    {
        $data = [
            stdClass::class => [
                'dummy{1..4, 2}' => [
                    'name' => '<username()>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        self::assertCount(0, $set->getParameters());

        $objects = $set->getObjects();
        self::assertCount(2, $objects);

        self::assertArrayHasKey('dummy1', $objects);
        self::assertArrayNotHasKey('dummy2', $objects);
        self::assertArrayHasKey('dummy3', $objects);
        self::assertArrayNotHasKey('dummy4', $objects);
    }

    public function testLoadReferenceRange(): void
    {
        $data = [
            User::class => [
                'usertemplate (template)' => [
                    'id' => '<uuid()>',
                ],
                'user0 (extends usertemplate)' => [
                    'name' => '<username()>',
                ],
                'user1' => [
                    'name' => '<username()>',
                ],
            ],
            UserDetail::class => [
                'userdetail_{@user*}' => [
                    'email' => '<email()>',
                    'user' => '<current()>',
                ],
                'userdetail_single_{@user1}' => [
                    'email' => '<email()>',
                    'user' => '<($current)>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        $objects = $set->getObjects();
        self::assertCount(5, $objects);

        self::assertArrayHasKey('userdetail_user0', $objects);
        self::assertArrayHasKey('userdetail_user1', $objects);
        self::assertArrayHasKey('userdetail_single_user1', $objects);

        self::assertInstanceOf(User::class, $objects['userdetail_user0']->getUser());
        self::assertInstanceOf(User::class, $objects['userdetail_user1']->getUser());
        self::assertInstanceOf(User::class, $objects['userdetail_single_user1']->getUser());

        self::assertSame($objects['user0'], $objects['userdetail_user0']->getUser());
        self::assertSame($objects['user1'], $objects['userdetail_user1']->getUser());
        self::assertSame($objects['user1'], $objects['userdetail_single_user1']->getUser());
    }

    public function testLoadReferenceRangeWithDotInName(): void
    {
        $data = [
            User::class => [
                'foo.user.{1..3}' => [
                    'id' => '<uuid()>',
                    'name' => '<username()>',
                ],
            ],
            UserDetail::class => [
                'foo.user_detail.{@foo.user.*}' => [
                    'email' => '<email()>',
                    'user' => '<current()>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        $objects = $set->getObjects();
        self::assertCount(6, $objects);

        self::assertArrayHasKey('foo.user.1', $objects);
        self::assertArrayHasKey('foo.user.2', $objects);
        self::assertArrayHasKey('foo.user.3', $objects);

        self::assertInstanceOf(User::class, $objects['foo.user_detail.foo.user.1']->getUser());
        self::assertInstanceOf(User::class, $objects['foo.user_detail.foo.user.2']->getUser());
        self::assertInstanceOf(User::class, $objects['foo.user_detail.foo.user.3']->getUser());

        self::assertSame($objects['foo.user.1'], $objects['foo.user_detail.foo.user.1']->getUser());
        self::assertSame($objects['foo.user.2'], $objects['foo.user_detail.foo.user.2']->getUser());
        self::assertSame($objects['foo.user.3'], $objects['foo.user_detail.foo.user.3']->getUser());
    }

    public function testLoadReferenceRangeOutOfOrder(): void
    {
        $data = [
            UserDetail::class => [
                'userdetail_{@user*}' => [
                    'email' => '<email()>',
                    'user' => '<current()>',
                ],
            ],
            User::class => [
                'usertemplate (template)' => [
                    'id' => '<uuid()>',
                ],
                'user0 (extends usertemplate)' => [
                    'name' => '<username()>',
                ],
                'user1' => [
                    'name' => '<username()>',
                ],
            ],
        ];

        $set = $this->loader->loadData($data);

        $objects = $set->getObjects();
        self::assertCount(4, $objects);

        self::assertArrayHasKey('userdetail_user0', $objects);
        self::assertArrayHasKey('userdetail_user1', $objects);

        self::assertInstanceOf(User::class, $objects['userdetail_user0']->getUser());
        self::assertInstanceOf(User::class, $objects['userdetail_user1']->getUser());

        self::assertSame($objects['user0'], $objects['userdetail_user0']->getUser());
        self::assertSame($objects['user1'], $objects['userdetail_user1']->getUser());
    }

    public function testTemplatesAreKeptBetweenFiles(): void
    {
        $expected = new ObjectSet(
            new ParameterBag(),
            new ObjectBag([
                'dummy' => StdClassFactory::create([
                    'foo' => 'bar',
                ]),
            ]),
        );
        $actual = $this->loader->loadFile(self::FIXTURES_FILES_DIR.'/template_in_another_file/dummy.yml');

        self::assertEquals($expected, $actual);
    }

    public function testLoadDuplicatedDummyKeysWithIncludeFiles(): void
    {
        $expected = new ObjectSet(
            new ParameterBag(),
            new ObjectBag([
                'dummy' => StdClassFactory::create([
                    'var' => 'bar',
                ]),
            ]),
        );

        $actual = $this->loader->loadFile(self::FIXTURES_FILES_DIR.'/duplicates/dummies.yml');

        self::assertEquals($expected, $actual);
    }

    public function testLoadDuplicatedDummyKeysWithLists(): void
    {
        $expected = new ObjectSet(
            new ParameterBag(),
            new ObjectBag([
                'dummy_A' => StdClassFactory::create([
                    'var' => 'A',
                ]),
                'dummy_B' => StdClassFactory::create([
                    'var' => 'foo',
                    'val' => 'val',
                ]),
                'another_dummy_A' => StdClassFactory::create([
                    'var' => 'foo',
                    'val' => 'val',
                ]),
                'another_dummy_B' => StdClassFactory::create([
                    'var' => 'foo',
                    'val' => 'val',
                ]),
            ]),
        );

        $actual = $this->loader->loadFile(self::FIXTURES_FILES_DIR.'/duplicates/dummy_list.yml');

        self::assertEquals($expected, $actual);
    }

    public function testTemplatesAreBuildBeforeUsage(): void
    {
        $expected = new ObjectSet(
            new ParameterBag(),
            new ObjectBag([
                'dummy' => new DummyWithConstructorAndCallable(null),
                'foo-0' => new DummyWithConstructorParam(null),
            ]),
        );

        $actual = $this->loader->loadData([
            DummyWithConstructorAndCallable::class => [
                'dummy_template (template)' => [
                    '__calls' => [
                        [
                            'reset' => [],
                        ],
                    ],
                ],
                'dummy (extends dummy_template)' => [
                    '__construct' => ['foo'],
                ],
            ],
            DummyWithConstructorParam::class => [
                'foo-0' => [
                    '__construct' => ['@dummy->foo'],
                ],
            ],
        ]);

        self::assertEquals($expected, $actual);
    }

    public function testTemplateCanExtendOtherTemplateObjectsCombinedWithRange(): void
    {
        $data = [
            stdClass::class => [
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
            ]),
        );
        $actual = $this->loader->loadData($data);

        self::assertEquals($expected, $actual);
    }

    public function testEmptyInheritance(): void
    {
        $data = [
            stdClass::class => [
                'dummy_template (template)' => [
                    'foo' => 'bar',
                ],
                'dummy (extends dummy_template)' => null,
            ],
        ];
        $expected = new ObjectSet(
            new ParameterBag(),
            new ObjectBag([
                'dummy' => StdClassFactory::create([
                    'foo' => 'bar',
                ]),
            ]),
        );
        $actual = $this->loader->loadData($data);

        self::assertEquals($expected, $actual);
    }

    public function testMultipleInheritanceInTemplates(): void
    {
        $data = [
            stdClass::class => [
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
            ]),
        );
        $actual = $this->loader->loadData($data);

        self::assertEquals($expected, $actual);
    }

    public function testMultipleInheritanceInInstance(): void
    {
        $data = [
            stdClass::class => [
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
            ]),
        );
        $actual = $this->loader->loadData($data);

        self::assertEquals($expected, $actual);
    }

    public function testUniqueValueGeneration(): void
    {
        $data = [
            stdClass::class => [
                'dummy{1..10}' => [
                    'number (unique)' => '<numberBetween(1, 10)>',
                ],
            ],
        ];

        $result = $this->loader->loadData($data);

        self::assertCount(0, $result->getParameters());
        self::assertCount(10, $result->getObjects());

        $objects = $result->getObjects();
        $value = [];
        foreach ($objects as $object) {
            self::assertGreaterThanOrEqual(1, $object->number);
            self::assertLessThanOrEqual(10, $object->number);
            $value[$object->number] = true;
        }

        self::assertCount(10, $value);
    }

    public function testUniqueValueGenerationFailure(): void
    {
        $data = [
            stdClass::class => [
                'dummy{1..10}' => [
                    'number (unique)' => '<numberBetween(1, 2)>',
                ],
            ],
        ];

        try {
            $this->loader->loadData($data);

            self::fail('Expected exception to be thrown.');
        } catch (GenerationThrowable $exception) {
            $previous = $exception->getPrevious();

            self::assertInstanceOf(UnresolvableValueDuringGenerationException::class, $previous);

            $previous = $previous->getPrevious();

            self::assertInstanceOf(UniqueValueGenerationLimitReachedException::class, $previous);
            self::assertMatchesRegularExpression(
                '/^Could not generate a unique value after 150 attempts for ".*"\.$/',
                $previous->getMessage(),
            );
        }
    }

    public function testUniqueValueGenerationInAFunctionCall(): void
    {
        $data = [
            FixtureEntity\Instantiator\DummyWithRequiredParameterInConstructor::class => [
                'dummy{1..10}' => [
                    '__construct' => [
                        '0 (unique)' => '<numberBetween(1, 10)>',
                    ],
                ],
            ],
        ];

        $result = $this->loader->loadData($data);

        self::assertCount(0, $result->getParameters());
        self::assertCount(10, $result->getObjects());

        $objects = $result->getObjects();
        $value = [];
        foreach ($objects as $object) {
            self::assertGreaterThanOrEqual(1, $object->requiredParam);
            self::assertLessThanOrEqual(10, $object->requiredParam);
            $value[$object->requiredParam] = true;
        }

        self::assertCount(10, $value);

        try {
            $this->loader->loadData([
                FixtureEntity\Instantiator\DummyWithRequiredParameterInConstructor::class => [
                    'dummy{1..10}' => [
                        '__construct' => [
                            '0 (unique)' => '<numberBetween(1, 2)>',
                        ],
                    ],
                ],
            ]);
            self::fail('Expected exception to be thrown.');
        } catch (GenerationThrowable $throwable) {
            self::assertInstanceOf(DebugUnexpectedValueException::class, $throwable);

            $previous = $throwable->getPrevious();

            self::assertInstanceOf(UnresolvableValueDuringGenerationException::class, $previous);

            self::assertInstanceOf(UniqueValueGenerationLimitReachedException::class, $previous->getPrevious());
        }
    }

    public function testUniqueValueGenerationWithDynamicArray(): void
    {
        $data = [
            stdClass::class => [
                'related_dummy{1..2}' => [
                    'name' => '<current()>',
                ],
                'dummy1' => [
                    'relatedDummies (unique)' => '2x @related_dummy*',
                ],
                'dummy2' => [
                    'relatedDummies (unique)' => '2x @related_dummy*',
                ],
            ],
        ];
        $result = $this->loader->loadData($data);

        $self = $this;
        $assertEachValuesInRelatedDummiesAreUnique = function (ObjectSet $set) use ($self): void {
            $self->assertCount(0, $set->getParameters());
            $self->assertCount(4, $set->getObjects());

            $dummy = $set->getObjects()['dummy1'];
            $self->assertCount(2, $dummy->relatedDummies);
            foreach ($dummy->relatedDummies as $relatedDummy) {
                $this->assertEquals($relatedDummy, $set->getObjects()['related_dummy'.$relatedDummy->name]);
            }

            $self->assertNotEquals($dummy->relatedDummies[0], $dummy->relatedDummies[1]);

            $anotherDummy = $set->getObjects()['dummy2'];
            $self->assertCount(2, $anotherDummy->relatedDummies);
            foreach ($anotherDummy->relatedDummies as $relatedDummy) {
                $this->assertEquals($relatedDummy, $set->getObjects()['related_dummy'.$relatedDummy->name]);
            }

            $self->assertNotEquals($anotherDummy->relatedDummies[0], $anotherDummy->relatedDummies[1]);
        };
        $assertEachValuesInRelatedDummiesAreUnique($result);

        // Do another check with range/list where a temporary fixture is being used for the unique key
        $data = [
            stdClass::class => [
                'related_dummy{1..2}' => [
                    'name' => '<current()>',
                ],
                'dummy{1..2}' => [
                    'relatedDummies (unique)' => '2x @related_dummy*',
                ],
            ],
        ];
        $result = $this->loader->loadData($data);

        $assertEachValuesInRelatedDummiesAreUnique($result);

        try {
            $this->loader->loadData([
                stdClass::class => [
                    'related_dummy' => [
                        'name' => 'unique',
                    ],
                    'dummy' => [
                        'relatedDummies (unique)' => '2x @related_dummy*',
                    ],
                ],
            ]);
            self::fail('Expected exception to be thrown.');
        } catch (GenerationThrowable $throwable) {
            self::assertInstanceOf(DebugUnexpectedValueException::class, $throwable);

            $previous = $throwable->getPrevious();

            self::assertInstanceOf(UnresolvableValueDuringGenerationException::class, $previous);

            self::assertInstanceOf(UniqueValueGenerationLimitReachedException::class, $previous->getPrevious());
        }
    }

    public function testUniqueOnArray(): void
    {
        $data = [
            stdClass::class => [
                'dummy' => [
                    'numbers (unique)' => [
                        1,
                        2,
                    ],
                ],
            ],
        ];
        $this->loader->loadData($data);

        $data = [
            stdClass::class => [
                'dummy' => [
                    'numbers (unique)' => [
                        1,
                        2,
                    ],
                ],
                'another_dummy' => [
                    'numbers (unique)' => [
                        1,
                        2,
                    ],
                ],
            ],
        ];
        $this->loader->loadData($data);

        try {
            $this->loader->loadData([
                stdClass::class => [
                    'dummy' => [
                        'numbers (unique)' => [
                            1,
                            1,
                        ],
                    ],
                ],
            ]);
            self::fail('Expected exception to be thrown.');
        } catch (GenerationThrowable $throwable) {
            self::assertInstanceOf(DebugUnexpectedValueException::class, $throwable);

            $previous = $throwable->getPrevious();

            self::assertInstanceOf(UnresolvableValueDuringGenerationException::class, $previous);

            self::assertInstanceOf(UniqueValueGenerationLimitReachedException::class, $previous->getPrevious());
        }
    }

    public function testUniqueValuesAreUniqueAcrossAClass(): void
    {
        $data = [
            stdClass::class => [
                'dummy{1..5}' => [
                    'val (unique)' => '<numberBetween(1, 10)>',
                ],
                'dummy{6..10}' => [
                    'val (unique)' => '<numberBetween(1, 10)>',
                ],
            ],
            FixtureEntity\DummyWithPublicProperty::class => [
                'dummy_with_public_property{1..10}' => [
                    'val (unique)' => '<numberBetween(1, 10)>',
                ],
            ],
        ];

        $result = $this->loader->loadData($data);

        self::assertCount(0, $result->getParameters());
        self::assertCount(20, $result->getObjects());

        $objects = $result->getObjects();
        $value = [
            stdClass::class => [],
            FixtureEntity\DummyWithPublicProperty::class => [],
        ];
        foreach ($objects as $object) {
            self::assertGreaterThanOrEqual(1, $object->val);
            self::assertLessThanOrEqual(10, $object->val);
            $value[$object::class][$object->val] = true;
        }

        self::assertCount(10, $value[stdClass::class]);
        self::assertCount(10, $value[FixtureEntity\DummyWithPublicProperty::class]);
    }

    /**
     * @see https://github.com/nelmio/alice/pull/950
     */
    public function testUniqueCircularReferencesThrowNoFatal(): void
    {
        $data = [
            stdClass::class => [
                'member{1..5}' => [
                    'id' => '<current()>',
                    'self' => '@self',
                ],
                'group' => [
                    'owner' => '@member*',
                    'members (unique)' => '100x @member*',
                ],
            ],
        ];

        try {
            $this->loader->loadData($data);
        } catch (DebugUnexpectedValueException $exception) {
            self::assertNotNull($previous = $exception->getPrevious());

            self::assertInstanceOf(UnresolvableValueDuringGenerationException::class, $previous);

            self::assertNotNull($previous = $previous->getPrevious());

            self::assertInstanceOf(UniqueValueGenerationLimitReachedException::class, $previous);
        }
    }

    public function testThrowsAnExceptionIfInheritFromAnNonExistingFixture(): void
    {
        $data = [
            stdClass::class => [
                'dummy (extends unknown)' => [],
            ],
        ];

        $this->expectException(FixtureNotFoundException::class);
        $this->expectExceptionMessage('Could not find the fixture "unknown".');

        $this->loader->loadData($data);
    }

    public function testThrowsAnExceptionIfInheritFromAnInexistingTemplate(): void
    {
        $data = [
            stdClass::class => [
                'another_dummy' => [],
                'dummy (extends another_dummy)' => [],
            ],
        ];

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fixture "dummy" extends "another_dummy" but "another_dummy" is not a template.');

        $this->loader->loadData($data);
    }

    public function testThrowsAnExceptionIfUsingCurrentOutOfACollection(): void
    {
        $data = [
            stdClass::class => [
                'dummy' => [
                    'foo' => '<current()>',
                ],
            ],
        ];

        try {
            $this->loader->loadData($data);

            self::fail('Expected exception to be thrown.');
        } catch (DebugUnexpectedValueException $exception) {
            self::assertSame(
                'An error occurred while generating the fixture "dummy" (stdClass): No value for '
                .'\'<current()>\' found for the fixture "dummy".',
                $exception->getMessage(),
            );

            $previous = $exception->getPrevious();

            self::assertInstanceOf(NoValueForCurrentException::class, $previous);
        }
    }

    public function testInjectedParametersAndObjectsAreOverriddenByLocalParameters(): void
    {
        $set = $this->loader->loadData(
            [
                'parameters' => [
                    'foz' => '<{foo}>',
                    'foo' => 'baz',
                ],
                DummyWithConstructorParam::class => [
                    'another_dummy' => [
                        '__construct' => ['@dummy'],
                    ],
                ],
                stdClass::class => [
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
                ]),
            ],
        );

        $expected = new ObjectSet(
            new ParameterBag([
                'foo' => 'baz',
                'foz' => 'baz',
            ]),
            new ObjectBag([
                'dummy' => $dummy = StdClassFactory::create([
                    'injected' => false,
                ]),
                'another_dummy' => new DummyWithConstructorParam($dummy),
            ]),
        );

        self::assertEquals($expected, $set);
    }

    public function testParametersShouldBeResolvedOnlyOnce(): void
    {
        $set = $this->loader->loadData(
            [
                'parameters' => [
                    'unique_id' => '<(uniqid())>',
                ],
                stdClass::class => [
                    'dummy' => [
                        'foo' => '<{unique_id}>',
                        'bar' => '<{unique_id}>',
                    ],
                ],
            ],
        );

        $uniqueId = $set->getParameters()['unique_id'];

        $dummy = $set->getObjects()['dummy'];

        self::assertEquals($uniqueId, $dummy->foo);
        self::assertEquals($uniqueId, $dummy->bar);
    }

    public function testLoadParsesReferencesInQuotes(): void
    {
        try {
            $this->loader->loadData([
                stdClass::class => [
                    'dummy1' => [
                        'name' => 'foo',
                    ],
                    'dummy2' => [
                        'dummy' => '\'@dummy1\'',
                    ],
                ],
            ]);

            self::fail('Expected exception to be thrown.');
        } catch (DebugUnexpectedValueException $exception) {
            self::assertInstanceOf(UnresolvableValueDuringGenerationException::class, $exception->getPrevious());
        }
    }

    /**
     * @testdox The cache of the loader
     */
    public function testGenerationCache(): void
    {
        // This loading will trigger the caching part of the FixtureWildcardReferenceResolver to
        // cache the pattern for `@another*`
        $this->nonIsolatedLoader->loadData(
            [
                'parameters' => [],
                stdClass::class => [
                    'another_dummy' => [],
                    'dummy' => [
                        'related' => '@another*',
                    ],
                ],
            ],
        );

        // This loading will also the caching part of the FixtureWildcardReferenceResolver. The
        // cache having the same lifetime as the generation context is what ensures the cache from
        // the previous loading will not interfer with this one
        $this->nonIsolatedLoader->loadData(
            [
                'parameters' => [],
                stdClass::class => [
                    'another_dummy_new' => [],
                    'dummy' => [
                        'related' => '@another*',
                    ],
                ],
            ],
        );
    }

    public function testInstancesAreNotInjectedInTheScopeDuringInstantiation(): void
    {
        try {
            $this->loader->loadData([
                stdClass::class => [
                    'dummy' => [],
                    'another_dummy' => [
                        '__construct' => [
                            '<(@dummy)>',
                        ],
                    ],
                ],
            ]);
        } catch (DebugUnexpectedValueException $exception) {
            self::assertSame(
                'An error occurred while generating the fixture "another_dummy" (stdClass): Could not resolve value during the generation process: Could not evaluate the expression "@dummy": Undefined variable $_instances',
                $exception->getMessage(),
            );

            $previous = $exception->getPrevious();

            self::assertInstanceOf(UnresolvableValueDuringGenerationException::class, $previous);
        }
    }

    public function testFunctionCallArgumentResolverWithObjectsKeepsSameInstances(): void
    {
        $set = $this->loader->loadData([
            stdClass::class => [
                'dummy1' => [
                    'foo' => 'bar',
                    'sibling' => '<(@dummy2)>',
                ],
                'dummy2' => [
                    'foo' => 'baz',
                ],
            ],
        ]);

        self::assertCount(2, $set->getObjects());
        ['dummy1' => $dummy1, 'dummy2' => $dummy2] = $set->getObjects();

        self::assertNotSame($dummy1, $dummy2);
        self::assertSame($dummy2, $dummy1->sibling);
    }

    public function testNewlinesInIdentity(): void
    {
        $objects = $this->loader->loadFile(self::FIXTURES_FILES_DIR.'/identity_newlines.yml')->getObjects();

        $expected = new stdClass();
        $expected->newlinesReplacedWithSpaces = new DateTime('2022-01-01');
        $expected->newlinesReplacedWithSpacesNoNewlineAtEnd = new DateTime('2022-01-02');
        $expected->newlinesReplacedWithSpacesAllNewlinesFromEnd = new DateTime('2022-01-03');
        $expected->newlinesKept = new DateTime('2022-01-04');
        $expected->newlinesKeptNoNewlineAtEnd = new DateTime('2022-01-05');
        $expected->newlinesKeptAllNewlinesFromEnd = new DateTime('2022-01-06');

        self::assertEquals(
            [
                'dummy' => $expected,
            ],
            $objects,
        );
    }

    public function testOptionalMethodCallsAreDeterministic(): void
    {
        $gen_sequence = function (NativeLoader $loader) {
            $set = $loader->loadData([
                FixtureEntity\Caller\Dummy::class => [
                    'dummy{0..9}' => [
                        '__construct' => false,
                        '__calls' => [
                            ['setTitle (50%?)' => ['Foo']],
                        ],
                    ],
                ],
            ]);
            $objs = array_values($set->getObjects());

            return array_map(
                fn ($obj) => (new ReflectionObject($obj))->getProperty('title')->getValue($obj),
                $objs,
            );
        };

        $seq_1 = $gen_sequence(new NativeLoader());
        $seq_2 = $gen_sequence(new NativeLoader());

        self::assertSame($seq_1, $seq_2);
    }

    public static function provideFixturesToInstantiate(): iterable
    {
        yield 'with default constructor – use default constructor' => [
            [
                FixtureEntity\Instantiator\DummyWithDefaultConstructor::class => [
                    'dummy' => [],
                ],
            ],
            new FixtureEntity\Instantiator\DummyWithDefaultConstructor(),
        ];

        yield 'with explicit default constructor - use constructor' => [
            [
                FixtureEntity\Instantiator\DummyWithExplicitDefaultConstructor::class => [
                    'dummy' => [],
                ],
            ],
            new FixtureEntity\Instantiator\DummyWithExplicitDefaultConstructor(),
        ];

        yield 'with default constructor and optional parameters without parameters - use constructor function' => [
            [
                FixtureEntity\Instantiator\DummyWithOptionalParameterInConstructor::class => [
                    'dummy' => [],
                ],
            ],
            new FixtureEntity\Instantiator\DummyWithOptionalParameterInConstructor(),
        ];

        yield 'with default constructor and optional parameters with parameters - use constructor function' => [
            [
                FixtureEntity\Instantiator\DummyWithOptionalParameterInConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            100,
                        ],
                    ],
                ],
            ],
            new FixtureEntity\Instantiator\DummyWithOptionalParameterInConstructor(100),
        ];

        yield 'with default constructor and required parameters with no parameters - throw exception' => [
            [
                FixtureEntity\Instantiator\DummyWithRequiredParameterInConstructor::class => [
                    'dummy' => [],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\Instantiator\DummyWithRequiredParameterInConstructor): Could not instantiate "dummy", the constructor has mandatory parameters but no parameters have been given.',
            GenerationThrowable::class,
        ];

        yield 'with default constructor and required parameters with parameters - use constructor function' => [
            [
                FixtureEntity\Instantiator\DummyWithRequiredParameterInConstructor::class => [
                    'dummy' => [
                        '__construct' => [100],
                    ],
                ],
            ],
            new FixtureEntity\Instantiator\DummyWithRequiredParameterInConstructor(100),
        ];

        yield 'with default constructor and required parameters with parameters and unique value - use constructor function' => [
            [
                FixtureEntity\Instantiator\DummyWithRequiredParameterInConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            '0 (unique)' => 100,
                        ],
                    ],
                ],
            ],
            new FixtureEntity\Instantiator\DummyWithRequiredParameterInConstructor(100),
        ];

        yield 'with private constructor – throw exception' => [
            [
                FixtureEntity\Instantiator\DummyWithPrivateConstructor::class => [
                    'dummy' => [],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\Instantiator\DummyWithPrivateConstructor): Could not instantiate "dummy", the constructor of "Nelmio\Alice\Entity\Instantiator\DummyWithPrivateConstructor" is not public.',
            GenerationThrowable::class,
        ];

        yield 'with protected constructor – throw exception' => [
            [
                FixtureEntity\Instantiator\DummyWithProtectedConstructor::class => [
                    'dummy' => [],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\Instantiator\DummyWithProtectedConstructor): Could not instantiate "dummy", the constructor of "Nelmio\Alice\Entity\Instantiator\DummyWithProtectedConstructor" is not public.',
            GenerationThrowable::class,
        ];

        yield 'with default constructor but specified no constructor – use reflection' => [
            [
                FixtureEntity\Instantiator\DummyWithDefaultConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new ReflectionClass(FixtureEntity\Instantiator\DummyWithDefaultConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with explicit constructor but specified no constructor – use reflection' => [
            [
                FixtureEntity\Instantiator\DummyWithExplicitDefaultConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new ReflectionClass(FixtureEntity\Instantiator\DummyWithExplicitDefaultConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with named constructor but specified no constructor – use reflection' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new ReflectionClass(FixtureEntity\Instantiator\DummyWithNamedConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with named constructor and optional parameters but specified no constructor – use reflection' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new ReflectionClass(FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with named constructor and required parameters but specified no constructor – use reflection' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new ReflectionClass(FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with optional parameters in constructor but specified no constructor – use reflection' => [
            [
                FixtureEntity\Instantiator\DummyWithOptionalParameterInConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new ReflectionClass(FixtureEntity\Instantiator\DummyWithOptionalParameterInConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with required parameters in constructor but specified no constructor – use reflection' => [
            [
                FixtureEntity\Instantiator\DummyWithRequiredParameterInConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new ReflectionClass(FixtureEntity\Instantiator\DummyWithRequiredParameterInConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with private constructor – use reflection' => [
            [
                FixtureEntity\Instantiator\DummyWithPrivateConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new ReflectionClass(FixtureEntity\Instantiator\DummyWithPrivateConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with protected constructor – use reflection' => [
            [
                FixtureEntity\Instantiator\DummyWithProtectedConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new ReflectionClass(FixtureEntity\Instantiator\DummyWithProtectedConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with private named constructor – use reflection' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedPrivateConstructor::class => [
                    'dummy' => [
                        '__construct' => false,
                    ],
                ],
            ],
            (new ReflectionClass(FixtureEntity\Instantiator\DummyWithNamedPrivateConstructor::class))->newInstanceWithoutConstructor(),
        ];

        yield 'with constructor named parameters' => [
            [
                FixtureEntity\DummyWithMethods::class => [
                    'dummy' => [
                        '__construct' => [
                            'foo2' => 'value 2',
                            'foo1' => 'value 1',
                        ],
                    ],
                ],
            ],
            new FixtureEntity\DummyWithMethods('value 1', 'value 2'),
        ];

        yield 'with factory named parameters' => [
            [
                FixtureEntity\DummyWithMethods::class => [
                    'dummy' => [
                        '__factory' => [
                            'create' => [
                                'foo2' => 'value 2',
                                'foo1' => 'value 1',
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\DummyWithMethods::create('value 1', 'value 2'),
        ];

        $expected = new FixtureEntity\DummyWithMethods('value 1', 'value 2');
        $expected->bar('value 3', 'value 4');

        yield 'with method call named parameters' => [
            [
                FixtureEntity\DummyWithMethods::class => [
                    'dummy' => [
                        '__construct' => [
                            'foo2' => 'value 2',
                            'foo1' => 'value 1',
                        ],
                        '__calls' => [
                            [
                                'bar' => [
                                    'bar2' => 'value 4',
                                    'bar1' => 'value 3',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $expected,
        ];
    }

    public static function provideLegacyFixturesToInstantiate(): iterable
    {
        yield 'with named constructor - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructor::namedConstruct(),
        ];

        yield 'with named constructor and optional parameters with no parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::namedConstruct(),
        ];

        yield 'with named constructor and optional parameters with parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::namedConstruct(100),
        ];

        yield 'with named constructor and optional parameters with parameters and unique value - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                '0 (unique)' => 100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::namedConstruct(100),
        ];

        yield 'with named constructor and required parameters with no parameters - throw exception' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\Instantiator\DummyWithNamedConstructorAndRequiredParameters): Could not instantiate fixture "dummy".',
            GenerationThrowable::class,
        ];

        yield 'with named constructor and required parameters with parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::namedConstruct(100),
        ];

        yield 'with named constructor and required parameters with named parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                'param' => 100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::namedConstruct(100),
        ];

        yield 'with unknown named constructor' => [
            [
                FixtureEntity\Instantiator\DummyWithDefaultConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            'unknown' => [],
                        ],
                    ],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\Instantiator\DummyWithDefaultConstructor): Could not instantiate fixture "dummy".',
            GenerationThrowable::class,
        ];

        yield 'with private named constructor – throw exception' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedPrivateConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\Instantiator\DummyWithNamedPrivateConstructor): Could not instantiate fixture "dummy".',
            GenerationThrowable::class,
        ];
    }

    public static function provideFixturesToInstantiateWithFactory(): iterable
    {
        yield 'regular factory' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructor::class => [
                    'dummy' => [
                        '__factory' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructor::namedConstruct(),
        ];

        yield 'factory with optional parameters with no parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__factory' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::namedConstruct(),
        ];

        yield 'factory with optional parameters with parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__factory' => [
                            'namedConstruct' => [
                                100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::namedConstruct(100),
        ];

        yield 'factory with optional parameters with parameters and unique value - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__factory' => [
                            'namedConstruct' => [
                                '0 (unique)' => 100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::namedConstruct(100),
        ];

        yield 'factory with required parameters with no parameters - throw exception' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__factory' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\Instantiator\DummyWithNamedConstructorAndRequiredParameters): Could not instantiate fixture "dummy".',
            GenerationThrowable::class,
        ];

        yield 'factory with required parameters with parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__factory' => [
                            'namedConstruct' => [
                                100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::namedConstruct(100),
        ];

        yield 'factory with required parameters with named parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__factory' => [
                            'namedConstruct' => [
                                'param' => 100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::namedConstruct(100),
        ];

        yield 'unknown named factory' => [
            [
                FixtureEntity\Instantiator\DummyWithDefaultConstructor::class => [
                    'dummy' => [
                        '__factory' => [
                            'unknown' => [],
                        ],
                    ],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\Instantiator\DummyWithDefaultConstructor): Could not instantiate fixture "dummy".',
            GenerationThrowable::class,
        ];

        yield 'with private factory – throw exception' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedPrivateConstructor::class => [
                    'dummy' => [
                        '__factory' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\Instantiator\DummyWithNamedPrivateConstructor): Could not instantiate fixture "dummy".',
            GenerationThrowable::class,
        ];
    }

    public static function provideFixtureToInstantiateWithDeprecatedConstructor(): iterable
    {
        yield 'with named constructor - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructor::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructor::namedConstruct(),
        ];

        yield 'with named constructor and optional parameters with no parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::namedConstruct(),
        ];

        yield 'with named constructor and optional parameters with parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::namedConstruct(100),
        ];

        yield 'with named constructor and optional parameters with parameters and unique value - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                '0 (unique)' => 100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndOptionalParameters::namedConstruct(100),
        ];

        yield 'with named constructor and required parameters with parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::namedConstruct(100),
        ];

        yield 'with named constructor and required parameters with named parameters - use factory function' => [
            [
                FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::class => [
                    'dummy' => [
                        '__construct' => [
                            'namedConstruct' => [
                                'param' => 100,
                            ],
                        ],
                    ],
                ],
            ],
            FixtureEntity\Instantiator\DummyWithNamedConstructorAndRequiredParameters::namedConstruct(100),
        ];
    }

    public static function provideFixturesToHydrate(): iterable
    {
        yield 'public camelCase property' => [
            [
                FixtureEntity\Hydrator\CamelCaseDummy::class => [
                    'dummy' => [
                        'publicProperty' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (static function (FixtureEntity\Hydrator\CamelCaseDummy $dummy) {
                    $dummy->publicProperty = 'bob';

                    return $dummy;
                })(new FixtureEntity\Hydrator\CamelCaseDummy()),
            ],
        ];

        yield 'public snake_case property' => [
            [
                FixtureEntity\Hydrator\SnakeCaseDummy::class => [
                    'dummy' => [
                        'public_property' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (static function (FixtureEntity\Hydrator\SnakeCaseDummy $dummy) {
                    $dummy->public_property = 'bob';

                    return $dummy;
                })(new FixtureEntity\Hydrator\SnakeCaseDummy()),
            ],
        ];

        yield 'public PascalCase property' => [
            [
                FixtureEntity\Hydrator\PascalCaseDummy::class => [
                    'dummy' => [
                        'PublicProperty' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (static function (FixtureEntity\Hydrator\PascalCaseDummy $dummy) {
                    $dummy->PublicProperty = 'bob';

                    return $dummy;
                })(new FixtureEntity\Hydrator\PascalCaseDummy()),
            ],
        ];

        yield 'public setter camelCase property' => [
            [
                FixtureEntity\Hydrator\CamelCaseDummy::class => [
                    'dummy' => [
                        'setterProperty' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (static function (FixtureEntity\Hydrator\CamelCaseDummy $dummy) {
                    $dummy->setSetterProperty('bob');

                    return $dummy;
                })(new FixtureEntity\Hydrator\CamelCaseDummy()),
            ],
        ];

        yield 'public setter snake_case property' => [
            [
                FixtureEntity\Hydrator\SnakeCaseDummy::class => [
                    'dummy' => [
                        'setter_property' => 'bob',
                    ],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\Hydrator\SnakeCaseDummy): Could not hydrate the property "setter_property" of the object "dummy", as the property does not exist (class: Nelmio\Alice\Entity\Hydrator\SnakeCaseDummy).',
            GenerationThrowable::class,
        ];

        yield 'magic call camelCase property' => [
            [
                FixtureEntity\Hydrator\MagicCallDummy::class => [
                    'dummy' => [
                        'magicProperty' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (static function (FixtureEntity\Hydrator\MagicCallDummy $dummy) {
                    $dummy->setMagicProperty('bob');

                    return $dummy;
                })(new FixtureEntity\Hydrator\MagicCallDummy()),
            ],
        ];

        yield 'magic call snake_case property' => [
            [
                FixtureEntity\Hydrator\MagicCallDummy::class => [
                    'dummy' => [
                        'magic_property' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (static function (FixtureEntity\Hydrator\MagicCallDummy $dummy) {
                    $dummy->setMagicProperty('bob');

                    return $dummy;
                })(new FixtureEntity\Hydrator\MagicCallDummy()),
            ],
        ];

        yield 'magic call PascalCase property' => [
            [
                FixtureEntity\Hydrator\MagicCallDummy::class => [
                    'dummy' => [
                        'MagicProperty' => 'bob',
                    ],
                ],
            ],
            [
                'dummy' => (static function (FixtureEntity\Hydrator\MagicCallDummy $dummy) {
                    $dummy->setMagicProperty('bob');

                    return $dummy;
                })(new FixtureEntity\Hydrator\MagicCallDummy()),
            ],
        ];
    }

    public static function provideFixturesToGenerate(): iterable
    {
        yield '[construct] with reference to object with throwable setter and caller' => [
            [
                FixtureEntity\OnceTimerDummy::class => [
                    'another_dummy' => [
                        'hydrate' => true,
                        '__calls' => [
                            ['call' => [true]],
                        ],
                    ],
                ],
                DummyWithConstructorParam::class => [
                    'dummy' => [
                        '__construct' => [
                            '@another_dummy',
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'another_dummy' => $yetAnotherDummy1 = (static function (FixtureEntity\OnceTimerDummy $anotherDummy1) {
                        $anotherDummy1->call(true);
                        $anotherDummy1->setHydrate(true);

                        return $anotherDummy1;
                    })(new FixtureEntity\OnceTimerDummy()),
                    'dummy' => $dummy1 = new DummyWithConstructorParam($yetAnotherDummy1),
                ],
            ],
        ];

        yield 'empty instance' => [
            [
                stdClass::class => [
                    'dummy' => [],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => new stdClass(),
                ],
            ],
        ];

        yield 'empty instance with null' => [
            [
                stdClass::class => [
                    'dummy' => null,
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => new stdClass(),
                ],
            ],
        ];

        yield 'static value' => [
            [
                stdClass::class => [
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
                    ]),
                ],
            ],
        ];

        yield 'reference value' => [
            [
                stdClass::class => [
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
                stdClass::class => [
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

        yield 'dynamic reference' => [
            [
                stdClass::class => [
                    'dummy{1..2}' => [
                        'name' => '<current()>',
                    ],
                    'another_dummy{1..2}' => [
                        'dummy' => '@dummy<current()>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy1' => $dummy1 = StdClassFactory::create([
                        'name' => '1',
                    ]),
                    'dummy2' => $dummy2 = StdClassFactory::create([
                        'name' => '2',
                    ]),
                    'another_dummy1' => StdClassFactory::create([
                        'dummy' => $dummy1,
                    ]),
                    'another_dummy2' => StdClassFactory::create([
                        'dummy' => $dummy2,
                    ]),
                ],
            ],
        ];

        yield 'inverted dynamic reference' => [
            [
                stdClass::class => [
                    'dummy{1..2}' => [
                        'relatedDummy' => '@another_dummy<current()>',
                    ],
                    'another_dummy{1..2}' => [
                        'name' => '<current()>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'another_dummy1' => $yetAnotherDummy1 = StdClassFactory::create([
                        'name' => '1',
                    ]),
                    'another_dummy2' => $anotherDummy2 = StdClassFactory::create([
                        'name' => '2',
                    ]),
                    'dummy1' => StdClassFactory::create([
                        'relatedDummy' => $yetAnotherDummy1,
                    ]),
                    'dummy2' => StdClassFactory::create([
                        'relatedDummy' => $anotherDummy2,
                    ]),
                ],
            ],
        ];

        yield 'dynamic reference with variable' => [
            [
                stdClass::class => [
                    'dummy{1..2}' => [
                        'name' => '<current()>',
                    ],
                    'another_dummy{1..2}' => [
                        'dummy' => '@dummy$current',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy1' => $dummy1 = StdClassFactory::create([
                        'name' => '1',
                    ]),
                    'dummy2' => $dummy2 = StdClassFactory::create([
                        'name' => '2',
                    ]),
                    'another_dummy1' => StdClassFactory::create([
                        'dummy' => $dummy1,
                    ]),
                    'another_dummy2' => StdClassFactory::create([
                        'dummy' => $dummy2,
                    ]),
                ],
            ],
        ];

        yield 'property reference value' => [
            [
                stdClass::class => [
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

        yield 'inverted property reference value' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'name' => '@another_dummy->name',
                    ],
                    'another_dummy' => [
                        'name' => 'foo',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'name' => 'foo',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'name' => 'foo',
                    ]),
                ],
            ],
        ];

        yield 'dynamic property reference value' => [
            [
                stdClass::class => [
                    'dummy{1..2}' => [
                        'name' => '<current()>',
                    ],
                    'another_dummy{1..2}' => [
                        'dummy' => '@dummy$current->name',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy1' => $dummy1 = StdClassFactory::create([
                        'name' => '1',
                    ]),
                    'dummy2' => $dummy2 = StdClassFactory::create([
                        'name' => '2',
                    ]),
                    'another_dummy1' => StdClassFactory::create([
                        'dummy' => '1',
                    ]),
                    'another_dummy2' => StdClassFactory::create([
                        'dummy' => '2',
                    ]),
                ],
            ],
        ];

        yield 'non existing property reference' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'foo' => '@dummy->bob',
                    ],
                ],
            ],
            'An error occurred while generating the fixture "another_dummy" (stdClass): Could not resolve value during the generation process: Could not find the property "bob" of the object "another_dummy" (class: stdClass).',
            GenerationThrowable::class,
        ];

        yield 'property reference value with a getter' => [
            [
                FixtureEntity\ValueResolver\DummyWithGetter::class => [
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
                    'dummy' => $dummy = (new FixtureEntity\ValueResolver\DummyWithGetter())->setName('foo'),
                    'another_dummy' => (new FixtureEntity\ValueResolver\DummyWithGetter())->setName('__get__foo'),
                ],
            ],
        ];

        yield 'inverted property reference value with a getter' => [
            [
                FixtureEntity\ValueResolver\DummyWithGetter::class => [
                    'dummy' => [
                        'name' => '@another_dummy->name',
                    ],
                    'another_dummy' => [
                        'name' => 'foo',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => $dummy = (new FixtureEntity\ValueResolver\DummyWithGetter())->setName('__get__foo'),
                    'another_dummy' => (new FixtureEntity\ValueResolver\DummyWithGetter())->setName('foo'),
                ],
            ],
        ];

        yield 'array value' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'dummies' => ['@dummy', '@dummy', '@dummy'],
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
                        'dummies' => [$dummy, $dummy, $dummy],
                    ]),
                ],
            ],
        ];

        yield 'wildcard reference value' => [
            [
                stdClass::class => [
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
                stdClass::class => [
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
                stdClass::class => [
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
                        'dummies' => [$dummy, $dummy, $dummy],
                    ]),
                ],
            ],
        ];

        yield 'string array value' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'dummies' => '[@dummy, @dummy, @dummy]',
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
                        'dummies' => [$dummy, $dummy, $dummy],
                    ]),
                ],
            ],
        ];

        yield 'dynamic array value with wildcard' => [
            [
                stdClass::class => [
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
                        'dummies' => [$dummy, $dummy, $dummy],
                    ]),
                ],
            ],
        ];

        yield 'dynamic array with fixture range' => [
            [
                stdClass::class => [
                    'dummy{1..3}' => [
                        'id' => '<current()>',
                    ],
                    'another_dummy' => [
                        'dummies' => '@dummy{1..2}',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy1' => $dummy1 = StdClassFactory::create([
                        'id' => '1',
                    ]),
                    'dummy2' => $dummy2 = StdClassFactory::create([
                        'id' => '2',
                    ]),
                    'dummy3' => $dummy3 = StdClassFactory::create([
                        'id' => '3',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'dummies' => [
                            $dummy1,
                            $dummy2,
                        ],
                    ]),
                ],
            ],
        ];

        yield 'objects with dots in their references' => [
            [
                stdClass::class => [
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
                stdClass::class => [
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
                stdClass::class => [
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
                stdClass::class => [
                    'dummy' => [
                        'foo' => '<numberBetween(0, 0)>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 0,
                    ]),
                ],
            ],
        ];

        yield '[function] call PHP native function' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => '<strtolower("BAR")>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                ],
            ],
        ];

        yield '[self reference] alone' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'itself' => '@self',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (static function () {
                        $dummy = new stdClass();
                        $dummy->itself = $dummy;

                        return $dummy;
                    })(),
                ],
            ],
        ];

        yield '[self reference] evaluated with a function' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'itself' => '@<("self")>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (static function () {
                        $dummy = new stdClass();
                        $dummy->itself = $dummy;

                        return $dummy;
                    })(),
                ],
            ],
        ];

        yield '[self reference] property' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                        'itself' => '@self',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (static function () {
                        $dummy = new stdClass();
                        $dummy->foo = 'bar';
                        $dummy->itself = $dummy;

                        return $dummy;
                    })(),
                ],
            ],
        ];

        yield 'identity provider' => [
            [
                stdClass::class => [
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

        yield '[variable] nominal' => [
            [
                FixtureEntity\DummyWithGetter::class => [
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
                    'dummy' => (static function (FixtureEntity\DummyWithGetter $dummy) {
                        $dummy->setFoo('bar');
                        $dummy->fooVal = 'bar';

                        return $dummy;
                    })(new FixtureEntity\DummyWithGetter()),
                    'another_dummy' => (static function (FixtureEntity\DummyWithGetter $dummy) {
                        $dummy->setFoo('bar');
                        $dummy->fooVal = 'rab';

                        return $dummy;
                    })(new FixtureEntity\DummyWithGetter()),
                ],
            ],
        ];

        yield '[variable] variables are scoped to the fixture' => [
            [
                FixtureEntity\DummyWithGetter::class => [
                    'dummy' => [
                        'foo' => 'bar',
                        'fooVal' => '$foo',
                    ],
                    'another_dummy' => [
                        'foo' => '$foo',
                    ],
                ],
            ],
            'An error occurred while generating the fixture "another_dummy" (Nelmio\Alice\Entity\DummyWithGetter): Could not resolve value during the generation process: Could not find a variable "$foo".',
            GenerationThrowable::class,
        ];

        yield '[identity] evaluate the argument as if it was a plain PHP function' => [
            [
                stdClass::class => [
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

        yield '[identity] invalid PHP expression' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => '<("Hello)>',
                    ],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (stdClass): Could not resolve value during the generation process: Could not evaluate the expression ""Hello": syntax error, unexpected end of file, expecting variable or "${" or "{$"',
            GenerationThrowable::class,
        ];

        yield '[identity] has access to variables' => [
            [
                stdClass::class => [
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

        yield '[identity] has access to fixtures' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'foo' => '<(@dummy->foo)>',
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

        yield '[identity] has access to instances' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => 'bar',
                    ],
                    'another_dummy' => [
                        'relatedDummy' => '<(@dummy)>',
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
                        'relatedDummy' => $dummy,
                    ]),
                ],
            ],
        ];

        yield '[identity] has access to current' => [
            [
                stdClass::class => [
                    'dummy_{1..2}' => [
                        'foo' => '<($current)>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy_1' => StdClassFactory::create([
                        'foo' => '1',
                    ]),
                    'dummy_2' => StdClassFactory::create([
                        'foo' => '2',
                    ]),
                ],
            ],
        ];

        yield '[templating] templates are not returned' => [
            [
                stdClass::class => [
                    'base_dummy (template)' => [],
                    'dummy' => [],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => new stdClass(),
                ],
            ],
        ];

        yield '[templating] nominal' => [
            [
                stdClass::class => [
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

        yield '[templating] reference range' => [
            [
                stdClass::class => [
                    'detailedDummy (template)' => [
                        'field' => 'value',
                    ],
                    'dummy_1' => [
                        'email' => 'dummy1@mail.com',
                    ],
                    'dummy_2' => [
                        'email' => 'dummy2@mail.com',
                    ],
                    'detailedDummy_{@dummy_*} (extends detailedDummy)' => [
                        'dummy' => '<current()>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy_1' => $dummy1 = StdClassFactory::create([
                        'email' => 'dummy1@mail.com',
                    ]),
                    'dummy_2' => $dummy2 = StdClassFactory::create([
                        'email' => 'dummy2@mail.com',
                    ]),
                    'detailedDummy_dummy_1' => StdClassFactory::create([
                        'field' => 'value',
                        'dummy' => $dummy1,
                    ]),
                    'detailedDummy_dummy_2' => StdClassFactory::create([
                        'field' => 'value',
                        'dummy' => $dummy2,
                    ]),
                ],
            ],
        ];

        yield '[current] nominal' => [
            [
                stdClass::class => [
                    'dummy{1..2}' => [
                        'val' => '<current()>',
                    ],
                    'dummy_{alice, bob}' => [
                        'val' => '<current()>',
                    ],
                    'dummy_var{1..2}' => [
                        'val' => '$current',
                    ],
                    'dummy_var_{alice, bob}' => [
                        'val' => '$current',
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
                    'dummy_var1' => StdClassFactory::create([
                        'val' => 1,
                    ]),
                    'dummy_var2' => StdClassFactory::create([
                        'val' => 2,
                    ]),
                    'dummy_var_alice' => StdClassFactory::create([
                        'val' => 'alice',
                    ]),
                    'dummy_var_bob' => StdClassFactory::create([
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

        yield 'at literal is not resolved' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'atValues' => [
                            '\@<("hello")>',
                            '\\\\',
                            '\\\\\@foo',
                            '\\\foo',
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'atValues' => [
                            '@hello',
                            '\\',
                            '\@foo',
                            '\foo',
                        ],
                    ]),
                ],
            ],
        ];

        yield '[parameter] simple' => [
            [
                'parameters' => [
                    'foo' => 'bar',
                ],
                stdClass::class => [
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
                stdClass::class => [
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
                stdClass::class => [
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

        yield '[parameter] circular reference' => [
            [
                'parameters' => [
                    'foo' => '<{bar}>',
                    'bar' => '<{foo}>',
                ],
            ],
            'Circular reference detected for the parameter "foo" while resolving ["foo", "bar"].',
            GenerationThrowable::class,
        ];

        yield 'parameters in identity' => [
            [
                'parameters' => [
                    'ping' => 'pong',
                    'foo' => 'bar',
                ],
                stdClass::class => [
                    'dummy' => [
                        'foo' => '<($ping)>',
                        'bar' => '$foo',
                    ],
                ],
            ],
            [
                'parameters' => [
                    'ping' => 'pong',
                    'foo' => 'bar',
                ],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'pong',
                        'bar' => 'pong',
                    ]),
                ],
            ],
        ];

        yield 'parameters in identity during instantiation' => [
            [
                'parameters' => [
                    'ping' => 'pong',
                    'foo' => 'bar',
                ],
                DummyWithConstructorParam::class => [
                    'dummy' => [
                        '__construct' => [
                            [
                                'foo' => '<($ping)>',
                                'bar' => '$foo',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [
                    'ping' => 'pong',
                    'foo' => 'bar',
                ],
                'objects' => [
                    'dummy' => new DummyWithConstructorParam([
                        'foo' => 'pong',
                        'bar' => 'bar',
                    ]),
                ],
            ],
        ];

        yield 'argument indexes' => [
            [
                'parameters' => [
                    'ping' => 'pong',
                    'foo' => 'bar',
                ],
                FixtureEntity\DummyWithVariadicConstructorParam::class => [
                    'dummy' => [
                        '__construct' => [
                            'foo' => '<($ping)>',
                            'bar' => '$foo',
                            '$bar',
                            '$3',
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [
                    'ping' => 'pong',
                    'foo' => 'bar',
                ],
                'objects' => [
                    'dummy' => new FixtureEntity\DummyWithVariadicConstructorParam(
                        'pong',
                        'pong',
                        'pong',
                        'pong',
                    ),
                ],
            ],
        ];

        yield 'argument indexes (ambiguous case, default to argument instead of factory)' => [
            [
                'parameters' => [],
                FixtureEntity\DummyWithVariadicConstructorParam::class => [
                    'dummy' => [
                        '__construct' => [
                            'foo' => 'bar',
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => new FixtureEntity\DummyWithVariadicConstructorParam(
                        'bar',
                    ),
                ],
            ],
        ];

        yield 'dynamic array with scalar value' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => '5x bar',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => ['bar', 'bar', 'bar', 'bar', 'bar'],
                    ]),
                ],
            ],
        ];

        yield 'dynamic array with scalar value with an invalid quantifier' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => '-5x bar',
                    ],
                ],
            ],
            'An error occurred while denormalizing the fixture "dummy" (stdClass): Invalid token "-5x bar" found.',
            LogicException::class,
        ];

        yield 'object circular reference' => [
            [
                DummyWithConstructorParam::class => [
                    'dummy' => [
                        '__construct' => [
                            '@another_dummy',
                        ],
                    ],
                    'another_dummy' => [
                        '__construct' => [
                            '@dummy',
                        ],
                    ],
                ],
            ],
            'An error occurred while generating the fixture "dummy" (Nelmio\Alice\Entity\DummyWithConstructorParam): Could not resolve value during the generation process: Circular reference detected for the parameter "another_dummy" while resolving ["another_dummy", "dummy"].',
            GenerationThrowable::class,
        ];

        yield 'has proper stdClass support' => [
            [
                stdClass::class => [
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
                    'dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                    'another_dummy' => StdClassFactory::create([
                        'foo' => 'bar',
                    ]),
                ],
            ],
        ];

        yield 'method calls' => [
            [
                FixtureEntity\Caller\Dummy::class => [
                    'dummy' => [
                        '__calls' => [
                            ['setTitle' => ['Fake Title']],
                            ['addFoo' => []],
                            ['addFoo' => []],
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => FixtureEntity\Caller\Dummy::create('Fake Title', 2),
                ],
            ],
        ];

        yield '[current] in method calls' => [
            [
                FixtureEntity\Caller\Dummy::class => [
                    'dummy_{1..2}' => [
                        '__calls' => [
                            ['setTitle' => ['Fake Title <current()>']],
                            ['addFoo' => []],
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy_1' => FixtureEntity\Caller\Dummy::create('Fake Title 1', 1),
                    'dummy_2' => FixtureEntity\Caller\Dummy::create('Fake Title 2', 1),
                ],
            ],
        ];

        yield 'reference value in method calls' => [
            [
                FixtureEntity\Caller\Dummy::class => [
                    'dummy_1' => [],
                    'dummy_2' => [
                        '__calls' => [
                            ['setTitle' => ['Dummy 2']],
                            ['setRelatedDummy' => ['@dummy_1']],
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy_1' => $dummy1 = FixtureEntity\Caller\Dummy::create(null, 0),
                    'dummy_2' => FixtureEntity\Caller\Dummy::create('Dummy 2', 0, $dummy1),
                ],
            ],
        ];

        yield 'method call reference value' => [
            [
                FixtureEntity\ValueResolver\DummyWithGetter::class => [
                    'dummy' => [
                        'name' => 'foobar',
                    ],
                ],
                stdClass::class => [
                    'another_dummy' => [
                        'foo' => '@dummy->getName()',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (new FixtureEntity\ValueResolver\DummyWithGetter())->setName('foobar'),
                    'another_dummy' => StdClassFactory::create([
                        'foo' => '__get__foobar',
                    ]),
                ],
            ],
        ];

        $dummyWithMethodArgument = new FixtureEntity\ValueResolver\DummyWithMethodArgument();
        $dummyWithMethodArgument->prefix = 'bazbaz__';
        yield 'method call reference value with reference argument' => [
            [
                FixtureEntity\ValueResolver\DummyWithGetter::class => [
                    'dummy' => [
                        'name' => 'foobar',
                    ],
                ],
                FixtureEntity\ValueResolver\DummyWithMethodArgument::class => [
                    'dummy_2' => [
                        'prefix' => 'bazbaz__',
                    ],
                ],
                stdClass::class => [
                    'another_dummy' => [
                        'foo' => '@dummy_2->getValue(@dummy)',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (new FixtureEntity\ValueResolver\DummyWithGetter())->setName('foobar'),
                    'dummy_2' => $dummyWithMethodArgument,
                    'another_dummy' => StdClassFactory::create([
                        'foo' => 'bazbaz____get__foobar',
                    ]),
                ],
            ],
        ];

        yield 'method call with another static function' => [
            [
                FixtureEntity\Caller\DummyWithStaticFunction::class => [
                    'dummy' => [
                        '__construct' => false,
                        '__calls' => [
                            [FixtureEntity\Caller\StaticService::class.'::setTitle' => ['@self', 'Foo']],
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => new FixtureEntity\Caller\DummyWithStaticFunction('Foo'),
                ],
            ],
        ];

        yield 'method call with optional flag' => [
            [
                FixtureEntity\Caller\DummyWithStaticFunction::class => [
                    'dummy' => [
                        '__construct' => false,
                        '__calls' => [
                            [FixtureEntity\Caller\StaticService::class.'::setTitle (0%?)' => ['@self', 'Foo']],
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => (new ReflectionClass(FixtureEntity\Caller\DummyWithStaticFunction::class))->newInstanceWithoutConstructor(),
                ],
            ],
        ];

        yield 'method call with static function' => [
            [
                FixtureEntity\Caller\DummyWithStaticFunction::class => [
                    'dummy' => [
                        '__construct' => false,
                        '__calls' => [
                            [FixtureEntity\Caller\DummyWithStaticFunction::class.'::setTitle' => ['@self', 'Foo']],
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => new FixtureEntity\Caller\DummyWithStaticFunction('Foo'),
                ],
            ],
        ];

        yield 'usage of percent sign in string (#665)' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => 'a\%b',
                        'bar' => '\%c',
                        'baz' => '100\%',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => 'a%b',
                        'bar' => '%c',
                        'baz' => '100%',
                    ]),
                ],
            ],
        ];

        yield 'usage of an object' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => (static fn () => StdClassFactory::create(['ping' => 'pong']))(),
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => StdClassFactory::create(['ping' => 'pong']),
                    ]),
                ],
            ],
        ];

        yield '[function] call nested PHP native function' => [
            [
                stdClass::class => [
                    'dummy' => [
                        'foo' => '<json_encode([])>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'foo' => '[]',
                    ]),
                ],
            ],
        ];

        yield '[configurator] named factory' => [
            [
                FixtureEntity\DummyWithImmutableFunction::class => [
                    'dummy' => [
                        '__construct' => false,
                        '__calls' => [
                            [
                                'withVal (configurator)' => [
                                    'foo',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'dummy' => new FixtureEntity\DummyWithImmutableFunction('foo'),
                ],
            ],
        ];

        // https://github.com/nelmio/alice/issues/752
        yield 'calls and factory order' => (static function () {
            return [
                [
                    FixtureEntity\InitializationOrder\Address::class => [
                        'address' => [
                            'country' => 'France',
                            'city' => 'Paris',
                        ],
                    ],
                    FixtureEntity\InitializationOrder\Person::class => [
                        'person' => [
                            '__factory' => [
                                'createWithAddress' => [
                                    '@address',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'parameters' => [],
                    'objects' => [
                        'address' => $address = (static function () {
                            $address = new FixtureEntity\InitializationOrder\Address();

                            $address->setCountry('France');
                            $address->setCity('Paris');

                            return $address;
                        })(),
                        'person' => FixtureEntity\InitializationOrder\Person::createWithAddress($address),
                    ],
                ],
            ];
        })();

        // https://github.com/nelmio/alice/issues/851
        yield 'construct with multiple references to objects with throwable setter' => (static function () {
            return [
                [
                    FixtureEntity\OnceTimerDummy::class => [
                        'dummy' => [
                            'relatedDummy' => '@anotherDummy',
                            'hydrate' => true,
                            '__calls' => [
                                ['call' => [true]],
                            ],
                        ],
                        'anotherDummy' => [
                            'relatedDummy' => '@yetAnotherDummy',
                            'hydrate' => true,
                            '__calls' => [
                                ['call' => [true]],
                            ],
                        ],
                        'yetAnotherDummy' => [
                            'hydrate' => true,
                            '__calls' => [
                                ['call' => [true]],
                            ],
                        ],
                    ],
                ],
                [
                    'parameters' => [],
                    'objects' => [
                        'yetAnotherDummy' => $yetAnotherDummy = (static function (FixtureEntity\OnceTimerDummy $dummy) {
                            $dummy->setHydrate(true);
                            $dummy->call(true);

                            return $dummy;
                        })(new FixtureEntity\OnceTimerDummy()),
                        'anotherDummy' => $anotherDummy = (static function (FixtureEntity\OnceTimerDummy $dummy, $relatedDummy) {
                            $dummy->setRelatedDummy($relatedDummy);
                            $dummy->setHydrate(true);
                            $dummy->call(true);

                            return $dummy;
                        })(new FixtureEntity\OnceTimerDummy(), $yetAnotherDummy),
                        'dummy' => $dummy = (static function (FixtureEntity\OnceTimerDummy $dummy, $relatedDummy) {
                            $dummy->setRelatedDummy($relatedDummy);
                            $dummy->setHydrate(true);
                            $dummy->call(true);

                            return $dummy;
                        })(new FixtureEntity\OnceTimerDummy(), $anotherDummy),
                    ],
                ],
            ];
        })();

        // https://github.com/nelmio/alice/issues/770
        yield 'typed parameters' => (static fn () => [
            [
                'parameters' => [
                    'intParam' => 100,
                    'stringParam' => '100',
                ],
                stdClass::class => [
                    'dummy' => [
                        'intParam' => '<{intParam}>',
                        'stringParam' => '<{stringParam}>',
                    ],
                ],
            ],
            [
                'parameters' => [
                    'intParam' => 100,
                    'stringParam' => '100',
                ],
                'objects' => [
                    'dummy' => StdClassFactory::create([
                        'intParam' => 100,
                        'stringParam' => '100',
                    ]),
                ],
            ],
        ])();

        // https://github.com/nelmio/alice/issues/894
        yield 'complex circular reference case' => (static function () {
            for ($i = 1; $i < 13; ++$i) {
                $var = 's'.$i;
                ${$var} = new stdClass();
            }

            $s1->related = [$s3, $s1];
            $s2->related = [$s2, $s10, $s8, $s9, $s11, $s6, $s4];
            $s3->related = [$s2, $s4, $s3, $s5, $s8, $s9, $s6];
            $s4->related = [$s2, $s4, $s7, $s9, $s11, $s8, $s6];
            $s5->related = [$s5, $s6];
            $s6->related = [$s6];
            $s7->related = [$s7, $s8];
            $s8->related = [$s8, $s2, $s9, $s4, $s3];
            $s9->related = [$s9, $s2, $s8];
            $s10->related = [$s10, $s2, $s8, $s3, $s4, $s11];
            $s11->related = [$s11, $s2, $s8];
            $s12->related = [$s12];

            return [
                [
                    stdClass::class => [
                        's1' => [
                            'related' => ['@s3', '@s1'],
                        ],
                        's2' => [
                            'related' => ['@s2', '@s10', '@s8', '@s9', '@s11', '@s6', '@s4'],
                        ],
                        's3' => [
                            'related' => ['@s2', '@s4', '@s3', '@s5', '@s8', '@s9', '@s6'],
                        ],
                        's4' => [
                            'related' => ['@s2', '@s4', '@s7', '@s9', '@s11', '@s8', '@s6'],
                        ],
                        's5' => [
                            'related' => ['@s5', '@s6'],
                        ],
                        's6' => [
                            'related' => ['@s6'],
                        ],
                        's7' => [
                            'related' => ['@s7', '@s8'],
                        ],
                        's8' => [
                            'related' => ['@s8', '@s2', '@s9', '@s4', '@s3'],
                        ],
                        's9' => [
                            'related' => ['@s9', '@s2', '@s8'],
                        ],
                        's10' => [
                            'related' => ['@s10', '@s2', '@s8', '@s3', '@s4', '@s11'],
                        ],
                        's11' => [
                            'related' => ['@s11', '@s2', '@s8'],
                        ],
                        's12' => [
                            'related' => ['@s12'],
                        ],
                    ],
                ],
                [
                    'parameters' => [],
                    'objects' => [
                        's1' => $s1,
                        's2' => $s2,
                        's3' => $s3,
                        's4' => $s4,
                        's5' => $s5,
                        's6' => $s6,
                        's7' => $s7,
                        's8' => $s8,
                        's9' => $s9,
                        's10' => $s10,
                        's11' => $s11,
                        's12' => $s12,
                    ],
                ],
            ];
        })();

        yield 'allow to instantiate an entity with an array arguments' => (static fn () => [
            [
                stdClass::class => [
                    'entity1' => [],
                    'entity2' => [],
                    'dummy' => [
                        'rootClass' => '<(new stdClass())>',
                        'instance' => '<(new Nelmio\Alice\Entity\DummyWithConstructorParam([@entity1, @entity2]))>',
                    ],
                ],
            ],
            [
                'parameters' => [],
                'objects' => [
                    'entity1' => new stdClass(),
                    'entity2' => new stdClass(),
                    'dummy' => StdClassFactory::create([
                        'rootClass' => new stdClass(),
                        'instance' => new DummyWithConstructorParam([new stdClass(), new stdClass()]),
                    ]),
                ],
            ],
        ])();
    }
}
