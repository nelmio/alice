<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\SimpleGenerator
 */
class SimpleGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAGenerator()
    {
        $this->assertTrue(is_a(SimpleGenerator::class, GeneratorInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new SimpleGenerator(new FakeFixtureSetResolver(), new FakeObjectGenerator());
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

        $generatedObjectProphecy = $this->prophesize(ObjectInterface::class);
        $generatedObjectProphecy->getReference()->willReturn('stdObject');
        $generatedObjectProphecy->getInstance()->willReturn(new \stdClass());
        /** @var ObjectInterface $generatedObject */
        $generatedObject = $generatedObjectProphecy->reveal();

        $objectGeneratorProphecy = $this->prophesize(ObjectGeneratorInterface::class);
        $objectGeneratorProphecy
            ->generate($fixture, $resolvedSet)
            ->willReturn($objects->with($generatedObject))
        ;
        /** @var ObjectGeneratorInterface $objectGenerator */
        $objectGenerator = $objectGeneratorProphecy->reveal();

        $expected = new ObjectSet($resolvedParameters, $objects->with($generatedObject));

        $generator = new SimpleGenerator($resolver, $objectGenerator);
        $actual = $generator->generate($set);

        $this->assertEquals($expected, $actual);

        $resolverProphecy->resolve(Argument::any())->shouldHaveBeenCalledTimes(1);
        $objectGeneratorProphecy->generate(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
