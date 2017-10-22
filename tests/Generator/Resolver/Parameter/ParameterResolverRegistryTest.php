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

namespace Nelmio\Alice\Generator\Resolver\Parameter;

use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\DummyChainableParameterResolverAwareResolver;
use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\FakeChainableParameterResolver;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Parameter\ParameterResolverRegistry
 */
class ParameterResolverRegistryTest extends TestCase
{
    public function testIsAParameterResolver()
    {
        $this->assertTrue(is_a(ParameterResolverRegistry::class, ParameterResolverInterface::class, true));
    }

    public function testAcceptsChainableParameterResolvers()
    {
        $resolverProphecy = $this->prophesize(ChainableParameterResolverInterface::class);
        $resolverProphecy->canResolve(Argument::any())->shouldNotBeCalled();
        /* @var ChainableParameterResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        new ParameterResolverRegistry([$resolver]);
    }

    public function testInjectsItselfToParameterResolverAwareResolvers()
    {
        $propRefl = (new ReflectionClass(ParameterResolverRegistry::class))->getProperty('resolvers');
        $propRefl->setAccessible(true);

        $oneResolver = new FakeChainableParameterResolver();
        $secondResolver = new DummyChainableParameterResolverAwareResolver();

        $registry = new ParameterResolverRegistry([$oneResolver, $secondResolver]);

        $this->assertSame($registry, $propRefl->getValue($registry)[1]->resolver);
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(ParameterResolverRegistry::class))->isCloneable());
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessage Expected resolvers to be "Nelmio\Alice\Generator\Resolver\ParameterResolverInterface" objects. Got "stdClass" instead.
     */
    public function testThrowsAnExceptionIfInvalidResolverIsPassed()
    {
        new ParameterResolverRegistry([new \stdClass()]);
    }

    public function testIteratesOverEveryResolverAndUsesTheFirstValidOne()
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
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage No resolver found to resolve parameter "foo".
     */
    public function testThrowsAnExceptionIfNoSuitableParserIsFound()
    {
        $registry = new ParameterResolverRegistry([]);
        $registry->resolve(new Parameter('foo', null), new ParameterBag(), new ParameterBag());
    }
}
