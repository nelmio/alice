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

use Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StaticParameterResolver;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Parameter\Chainable\StaticParameterResolver
 */
class StaticParameterResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableParameterResolver()
    {
        $this->assertTrue(is_a(StaticParameterResolver::class, ChainableParameterResolverInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new StaticParameterResolver();
    }

    public function testCanOnlyResolveSimpleValues()
    {
        $resolver = new StaticParameterResolver();
        $parameter = new Parameter('foo', null);

        $this->assertTrue($resolver->canResolve($parameter->withValue(null)));
        $this->assertTrue($resolver->canResolve($parameter->withValue(10)));
        $this->assertTrue($resolver->canResolve($parameter->withValue(.75)));
        $this->assertTrue($resolver->canResolve($parameter->withValue(new \stdClass())));
        $this->assertTrue($resolver->canResolve($parameter->withValue(function () {})));

        $this->assertFalse($resolver->canResolve($parameter->withValue('string')));
    }

    public function testReturnsResolvedParameter()
    {
        $parameter = new Parameter('foo', null);
        $resolver = new StaticParameterResolver();

        $result = $resolver->resolve($parameter, new ParameterBag(), new ParameterBag());

        $this->assertEquals(
            new ParameterBag([
                'foo' => null,
            ]),
            $result
        );
    }
}
