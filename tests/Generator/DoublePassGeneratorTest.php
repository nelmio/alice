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

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Entity\StdClassFactory;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\DoublePassGenerator
 */
class DoublePassGeneratorTest extends TestCase
{
    public function testIsAGenerator()
    {
        $this->assertTrue(is_a(DoublePassGenerator::class, GeneratorInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(DoublePassGenerator::class))->isCloneable());
    }

    public function testGenerateObjects()
    {
        $loadedParameters = new ParameterBag(['loaded' => true]);
        $injectedParameters = new ParameterBag(['injected' => true]);

        $fixture = new DummyFixture('dummy');
        $fixtures = (new FixtureBag())->with($fixture);

        $objects = new ObjectBag([
            'std' => new \stdClass(),
        ]);

        $set = new FixtureSet($loadedParameters, $injectedParameters, $fixtures, $objects);

        $resolvedParameters = $injectedParameters->with(new Parameter('loaded', true));
        $resolvedSet = new ResolvedFixtureSet($resolvedParameters, $fixtures, $objects);

        $resolverProphecy = $this->prophesize(FixtureSetResolverInterface::class);
        $resolverProphecy->resolve($set)->willReturn($resolvedSet);
        /** @var FixtureSetResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $context = new GenerationContext();

        $objectGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
        $objectGeneratorProphecy
            ->generate($fixture, $resolvedSet, $context)
            ->willReturn($objectsAfterFirstPass = $objects->with(
                new SimpleObject(
                    'foo',
                    StdClassFactory::create(['pass' => 'first'])
                )
            ))
        ;
        $contextAfterFirstPass = clone $context;
        $contextAfterFirstPass->setToSecondPass();
        $objectGeneratorProphecy
            ->generate(
                $fixture,
                new ResolvedFixtureSet(
                    $resolvedSet->getParameters(),
                    $resolvedSet->getFixtures(),
                    $objectsAfterFirstPass
                ),
                $contextAfterFirstPass
            )
            ->willReturn($objectsAfterFirstPass = $objects->with(
                new SimpleObject(
                    'foo',
                    StdClassFactory::create(['pass' => 'second'])
                )
            ))
        ;
        /** @var ObjectGeneratorInterface $objectGenerator */
        $objectGenerator = $objectGeneratorProphecy->reveal();

        $expected = new ObjectSet(
            $resolvedParameters,
            $objects->with(
                new SimpleObject(
                    'foo',
                    StdClassFactory::create(['pass' => 'second'])
                )
            )
        );

        $generator = new DoublePassGenerator($resolver, $objectGenerator);
        $actual = $generator->generate($set);

        $this->assertEquals($expected, $actual);

        $resolverProphecy->resolve(Argument::any())->shouldHaveBeenCalledTimes(1);
        $objectGeneratorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(2);
    }
}
