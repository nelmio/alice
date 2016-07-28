<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Parameter;

use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\Generator\Resolver\Parameter\ParameterResolverRegistry
 */
class ParameterResolverRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAParameterResolver()
    {
        $this->assertTrue(is_a(ParameterResolverRegistry::class, ParameterResolverInterface::class, true));
    }

    public function testAcceptChainableParameterResolvers()
    {
        $resolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $resolverProphecy->canResolve(Argument::any())->shouldNotBeCalled();
        /* @var ChainableParameterResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        new ParameterResolverRegistry([$resolver]);
    }

    public function testInjectItselfToParameterResolverAwareResolvers()
    {
        $resolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $resolverProphecy->canResolve(Argument::any())->shouldNotBeCalled();
        /* @var ChainableParameterResolverInterface $oneResolver */
        $oneResolver = $resolverProphecy->reveal();

        $secondResolver = new DummyChainableResolverAwareResolver();

        $registry = new ParameterResolverRegistry([$oneResolver, $secondResolver]);

        $this->assertSame($registry, $secondResolver->resolver);
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $registry = new ParameterResolverRegistry([]);
        clone $registry;
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage Expected resolvers to be "Nelmio\Alice\Generator\Resolver\ParameterResolverInterface" objects. Got
     *                           "stdClass" instead.
     */
    public function testThrowExceptionIfInvalidParserIsPassed()
    {
        new ParameterResolverRegistry([new \stdClass()]);
    }

    public function testIterateOverEveryParsersAndUseTheFirstValidOne()
    {
        $parameter = new Parameter('foo', null);
        $expected = new ParameterBag(['foo' => 'bar']);

        $resolver1Prophecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $resolver1Prophecy->canResolve($parameter)->willReturn(false);
        /* @var ChainableParameterResolverInterface $resolver1 */
        $resolver1 = $resolver1Prophecy->reveal();

        $resolver2Prophecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $resolver2Prophecy->canResolve($parameter)->willReturn(true);
        $resolver2Prophecy->resolve(Argument::cetera())->willReturn($expected);
        /* @var ChainableParameterResolverInterface $resolver2 */
        $resolver2 = $resolver2Prophecy->reveal();

        $resolver3Prophecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $resolver3Prophecy->canResolve(Argument::any())->shouldNotBeCalled();
        /* @var ChainableParameterResolverInterface $resolver */
        $resolver = $resolver3Prophecy->reveal();

        $registry = new ParameterResolverRegistry([
            $resolver1,
            $resolver2,
            $resolver,
        ]);
        $actual = $registry->resolve($parameter, new ParameterBag(), new ParameterBag());

        $this->assertSame($expected, $actual);

        $resolver1Prophecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(1);
        $resolver2Prophecy->canResolve(Argument::any())->shouldHaveBeenCalledTimes(1);
        $resolver2Prophecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage No suitable resolver found for the parameter "foo".
     */
    public function testThrowExceptionIfNoSuitableParserIsFound()
    {
        $registry = new ParameterResolverRegistry([]);
        $registry->resolve(new Parameter('foo', null), new ParameterBag(), new ParameterBag());
    }
}

final class DummyChainableResolverAwareResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    public $resolver;

    public function canResolve(Parameter $parameter): bool
    {
        throw new \BadMethodCallException();
    }

    public function withResolver(ParameterResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters
    ): ParameterBag
    {
        throw new \BadMethodCallException();
    }
}
