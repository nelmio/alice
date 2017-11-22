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
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\ObjectGenerator\CompleteObjectGenerator
 */
class CompleteObjectGeneratorTest extends TestCase
{
    public function testIsAnObjectGenerator()
    {
        $this->assertTrue(is_a(CompleteObjectGenerator::class, ObjectGeneratorInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(CompleteObjectGenerator::class))->isCloneable());
    }

    public function testReturnsFixtureSetObjectsIfObjectIsAlreadyCompletelyGenerated()
    {
        $fixture = new DummyFixture('dummy');
        $set = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())->with($fixture),
            $expected = new ObjectBag(['dummy' => new \stdClass()])
        );
        $context = new GenerationContext();

        $generator = new CompleteObjectGenerator(new FakeObjectGenerator());
        $actual = $generator->generate($fixture, $set, $context);

        $this->assertEquals($expected, $actual);
    }

    public function testUsesDecoratedGeneratorToGenerateTheObjectAndReturnsTheResultIfDoesNotRequireACompleteObject()
    {
        $fixture = new SimpleFixture(
            'dummy',
            'Dummy',
            SpecificationBagFactory::create(
                null,
                (new PropertyBag())->with(new Property('foo', 'bar'))
            )
        );
        $set = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())->with($fixture)
        );
        $context = new GenerationContext();

        $decoratedGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
        $decoratedGeneratorProphecy
            ->generate($fixture, $set, $context)
            ->willReturn(
                $expected = (new ObjectBag())->with(new SimpleObject('dummy', new \stdClass()))
            )
        ;
        /** @var ObjectGeneratorInterface $decoratedGenerator */
        $decoratedGenerator = $decoratedGeneratorProphecy->reveal();

        $generator = new CompleteObjectGenerator($decoratedGenerator);
        $actual = $generator->generate($fixture, $set, $context);

        $this->assertEquals($expected, $actual);

        $decoratedGeneratorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @dataProvider provideSets
     */
    public function testReturnsCompleteObjectWheneverItIsPossible(
        FixtureInterface $fixture,
        GenerationContext $context,
        ObjectGeneratorInterface $decoratedGenerator,
        ObjectBag $expected
    ) {
        $set = ResolvedFixtureSetFactory::create(
            null,
            (new FixtureBag())->with($fixture)
        );

        $generator = new CompleteObjectGenerator($decoratedGenerator);
        $actual = $generator->generate($fixture, $set, $context);

        $this->assertEquals($expected, $actual);
    }

    public function provideSets()
    {
        yield 'decorated generator generates a complete object => complete object' => (function () {
            $fixture = new SimpleFixture(
                'dummy',
                'Dummy',
                SpecificationBagFactory::create(
                        null,
                        (new PropertyBag())->with(new Property('foo', 'bar'))
                    )
            );

            $context = new GenerationContext();

            $decoratedGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
            $decoratedGeneratorProphecy
                    ->generate(Argument::cetera())
                    ->willReturn(
                        (new ObjectBag())->with(
                            new CompleteObject(
                                new SimpleObject('dummy', new \stdClass())
                            )
                        )
                    )
                ;
            /** @var ObjectGeneratorInterface $decoratedGenerator */
            $decoratedGenerator = $decoratedGeneratorProphecy->reveal();

            $expected = (new ObjectBag())->with(
                new CompleteObject(
                        new CompleteObject(
                            new SimpleObject('dummy', new \stdClass())
                        )
                    )
            );

            return [
                    $fixture,
                    $context,
                    $decoratedGenerator,
                    $expected,
                ];
        })();

        yield 'object has been generated during the second pass => complete object' => (function () {
            $fixture = new SimpleFixture(
                'dummy',
                'Dummy',
                SpecificationBagFactory::create(
                        null,
                        (new PropertyBag())->with(new Property('foo', 'bar'))
                    )
            );

            $context = new GenerationContext();
            $context->setToSecondPass();

            $decoratedGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
            $decoratedGeneratorProphecy
                    ->generate(Argument::cetera())
                    ->willReturn(
                        (new ObjectBag())->with(
                            new SimpleObject('dummy', new \stdClass())
                        )
                    )
                ;
            /** @var ObjectGeneratorInterface $decoratedGenerator */
            $decoratedGenerator = $decoratedGeneratorProphecy->reveal();

            $expected = (new ObjectBag())->with(
                new CompleteObject(
                        new SimpleObject('dummy', new \stdClass())
                    )
            );

            return [
                    $fixture,
                    $context,
                    $decoratedGenerator,
                    $expected,
                ];
        })();

        yield 'object was generated with "complete object" generation context => complete object' => (function () {
            $fixture = new SimpleFixture(
                'dummy',
                'Dummy',
                SpecificationBagFactory::create(
                        null,
                        (new PropertyBag())->with(new Property('foo', 'bar'))
                    )
            );

            $context = new GenerationContext();
            $context->markAsNeedsCompleteGeneration();

            $decoratedGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
            $decoratedGeneratorProphecy
                    ->generate(Argument::cetera())
                    ->willReturn(
                        (new ObjectBag())->with(
                            new SimpleObject('dummy', new \stdClass())
                        )
                    )
                ;
            /** @var ObjectGeneratorInterface $decoratedGenerator */
            $decoratedGenerator = $decoratedGeneratorProphecy->reveal();

            $expected = (new ObjectBag())->with(
                new CompleteObject(
                        new SimpleObject('dummy', new \stdClass())
                    )
            );

            return [
                    $fixture,
                    $context,
                    $decoratedGenerator,
                    $expected,
                ];
        })();

        yield 'object generated needed only instantiation => complete object' => (function () {
            $fixture = new SimpleFixture(
                'dummy',
                'Dummy',
                SpecificationBagFactory::create()
            );

            $context = new GenerationContext();

            $decoratedGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
            $decoratedGeneratorProphecy
                    ->generate(Argument::cetera())
                    ->willReturn(
                        (new ObjectBag())->with(
                            new SimpleObject('dummy', new \stdClass())
                        )
                    )
                ;
            /** @var ObjectGeneratorInterface $decoratedGenerator */
            $decoratedGenerator = $decoratedGeneratorProphecy->reveal();

            $expected = (new ObjectBag())->with(
                new CompleteObject(
                        new SimpleObject('dummy', new \stdClass())
                    )
            );

            return [
                    $fixture,
                    $context,
                    $decoratedGenerator,
                    $expected,
                ];
        })();

        yield 'object generated during first pass => unchanged' => (function () {
            $fixture = new SimpleFixture(
                'dummy',
                'Dummy',
                SpecificationBagFactory::create(
                        null,
                        (new PropertyBag())->with(new Property('foo', 'bar'))
                    )
            );

            $context = new GenerationContext();

            $decoratedGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
            $decoratedGeneratorProphecy
                    ->generate(Argument::cetera())
                    ->willReturn(
                        $expected = (new ObjectBag())->with(
                            new SimpleObject('dummy', new \stdClass())
                        )
                    )
                ;
            /** @var ObjectGeneratorInterface $decoratedGenerator */
            $decoratedGenerator = $decoratedGeneratorProphecy->reveal();

            return [
                    $fixture,
                    $context,
                    $decoratedGenerator,
                    $expected,
                ];
        })();
    }
}
