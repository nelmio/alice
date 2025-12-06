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

namespace Nelmio\Alice\Generator\ObjectGenerator;

use Closure;
use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Object\CompleteObject;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\FakeObjectGenerator;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ObjectGeneratorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\ObjectBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(CompleteObjectGenerator::class)]
final class CompleteObjectGeneratorTest extends TestCase
{
    use ProphecyTrait;

    public function testIsAnObjectGenerator(): void
    {
        self::assertTrue(is_a(CompleteObjectGenerator::class, ObjectGeneratorInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(CompleteObjectGenerator::class))->isCloneable());
    }

    public function testReturnsFixtureSetObjectsIfObjectIsAlreadyCompletelyGenerated(): void
    {
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())->with($fixture),
            $expected = new ObjectBag(['dummy' => new stdClass()]),
        );
        $context = new GenerationContext();

        $generator = new CompleteObjectGenerator(new FakeObjectGenerator());
        $actual = $generator->generate($fixture, $set, $context);

        self::assertEquals($expected, $actual);
    }

    public function testUsesDecoratedGeneratorToGenerateTheObjectAndReturnsTheResultIfDoesNotRequireACompleteObject(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            'Dummy',
            SpecificationBagFactory::create(
                null,
                (new PropertyBag())->with(new Property('foo', 'bar')),
            ),
        );
        $set = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())->with($fixture),
        );
        $context = new GenerationContext();

        $decoratedGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
        $decoratedGeneratorProphecy
            ->generate($fixture, $set, $context)
            ->willReturn(
                $expected = (new ObjectBag())->with(new SimpleObject('dummy', new stdClass())),
            );
        /** @var ObjectGeneratorInterface $decoratedGenerator */
        $decoratedGenerator = $decoratedGeneratorProphecy->reveal();

        $generator = new CompleteObjectGenerator($decoratedGenerator);
        $actual = $generator->generate($fixture, $set, $context);

        self::assertEquals($expected, $actual);

        $decoratedGeneratorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideSets')]
    public function testReturnsCompleteObjectWheneverItIsPossible(
        FixtureInterface $fixture,
        GenerationContext $context,
        Closure $decoratedGeneratorFactory,
        ObjectBag $expected
    ): void {
        $set = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())->with($fixture),
        );
        $decoratedGenerator = $decoratedGeneratorFactory($this);

        $generator = new CompleteObjectGenerator($decoratedGenerator);
        $actual = $generator->generate($fixture, $set, $context);

        self::assertEquals($expected, $actual);
    }

    public static function provideSets(): iterable
    {
        yield 'decorated generator generates a complete object => complete object' => (function () {
            $fixture = new SimpleFixture(
                'dummy',
                'Dummy',
                SpecificationBagFactory::create(
                    null,
                    (new PropertyBag())->with(new Property('foo', 'bar')),
                ),
            );

            $context = new GenerationContext();

            $decoratedGeneratorFactory = static function (self $testCase): ObjectGeneratorInterface {
                $decoratedGeneratorProphecy = $testCase->prophesize(ObjectGeneratorInterface::class);
                $decoratedGeneratorProphecy
                    ->generate(Argument::cetera())
                    ->willReturn(
                        (new ObjectBag())->with(
                            new CompleteObject(
                                new SimpleObject('dummy', new stdClass()),
                            ),
                        ),
                    );

                return $decoratedGeneratorProphecy->reveal();
            };

            $expected = (new ObjectBag())->with(
                new CompleteObject(
                    new CompleteObject(
                        new SimpleObject('dummy', new stdClass()),
                    ),
                ),
            );

            return [
                $fixture,
                $context,
                $decoratedGeneratorFactory,
                $expected,
            ];
        })();

        yield 'object has been generated during the second pass => complete object' => (function () {
            $fixture = new SimpleFixture(
                'dummy',
                'Dummy',
                SpecificationBagFactory::create(
                    null,
                    (new PropertyBag())->with(new Property('foo', 'bar')),
                ),
            );

            $context = new GenerationContext();
            $context->setToSecondPass();

            $decoratedGeneratorFactory = static function (self $testCase): ObjectGeneratorInterface {
                $decoratedGeneratorProphecy = $testCase->prophesize(ObjectGeneratorInterface::class);
                $decoratedGeneratorProphecy
                    ->generate(Argument::cetera())
                    ->willReturn(
                        (new ObjectBag())->with(
                            new SimpleObject('dummy', new stdClass()),
                        ),
                    );

                return $decoratedGeneratorProphecy->reveal();
            };

            $expected = (new ObjectBag())->with(
                new CompleteObject(
                    new SimpleObject('dummy', new stdClass()),
                ),
            );

            return [
                $fixture,
                $context,
                $decoratedGeneratorFactory,
                $expected,
            ];
        })();

        yield 'object was generated with "complete object" generation context => complete object' => (function () {
            $fixture = new SimpleFixture(
                'dummy',
                'Dummy',
                SpecificationBagFactory::create(
                    null,
                    (new PropertyBag())->with(new Property('foo', 'bar')),
                ),
            );

            $context = new GenerationContext();
            $context->markAsNeedsCompleteGeneration();

            $decoratedGeneratorFactory = static function (self $testCase): ObjectGeneratorInterface {
                $decoratedGeneratorProphecy = $testCase->prophesize(ObjectGeneratorInterface::class);
                $decoratedGeneratorProphecy
                    ->generate(Argument::cetera())
                    ->willReturn(
                        (new ObjectBag())->with(
                            new SimpleObject('dummy', new stdClass()),
                        ),
                    );

                return $decoratedGeneratorProphecy->reveal();
            };

            $expected = (new ObjectBag())->with(
                new CompleteObject(
                    new SimpleObject('dummy', new stdClass()),
                ),
            );

            return [
                $fixture,
                $context,
                $decoratedGeneratorFactory,
                $expected,
            ];
        })();

        yield 'object generated needed only instantiation => complete object' => (function () {
            $fixture = new SimpleFixture(
                'dummy',
                'Dummy',
                SpecificationBagFactory::create(),
            );

            $context = new GenerationContext();

            $decoratedGeneratorFactory = static function (self $testCase): ObjectGeneratorInterface {
                $decoratedGeneratorProphecy = $testCase->prophesize(ObjectGeneratorInterface::class);
                $decoratedGeneratorProphecy
                    ->generate(Argument::cetera())
                    ->willReturn(
                        (new ObjectBag())->with(
                            new SimpleObject('dummy', new stdClass()),
                        ),
                    );

                return $decoratedGeneratorProphecy->reveal();
            };

            $expected = (new ObjectBag())->with(
                new CompleteObject(
                    new SimpleObject('dummy', new stdClass()),
                ),
            );

            return [
                $fixture,
                $context,
                $decoratedGeneratorFactory,
                $expected,
            ];
        })();

        yield 'object generated during first pass => unchanged' => (function () {
            $fixture = new SimpleFixture(
                'dummy',
                'Dummy',
                SpecificationBagFactory::create(
                    null,
                    (new PropertyBag())->with(new Property('foo', 'bar')),
                ),
            );

            $context = new GenerationContext();

            $expected = (new ObjectBag())->with(
                new SimpleObject('dummy', new stdClass()),
            );

            $decoratedGeneratorFactory = static function (self $testCase) use ($expected): ObjectGeneratorInterface {
                $decoratedGeneratorProphecy = $testCase->prophesize(ObjectGeneratorInterface::class);
                $decoratedGeneratorProphecy
                    ->generate(Argument::cetera())
                    ->willReturn($expected);

                return $decoratedGeneratorProphecy->reveal();
            };

            return [
                $fixture,
                $context,
                $decoratedGeneratorFactory,
                $expected,
            ];
        })();
    }
}
