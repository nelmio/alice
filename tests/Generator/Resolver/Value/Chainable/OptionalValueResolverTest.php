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

namespace Nelmio\Alice\Generator\Resolver\Value\Chainable;

use Nelmio\Alice\Definition\Fixture\FakeFixture;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use Nelmio\Alice\Definition\Value\OptionalValue;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\Resolver\Value\ChainableValueResolverInterface;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver
 */
class OptionalValueResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableResolver()
    {
        $this->assertTrue(is_a(OptionalValueResolver::class, ChainableValueResolverInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new OptionalValueResolver();
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $resolver = new OptionalValueResolver();
        $newResolver = $resolver->withValueResolver(new FakeValueResolver());

        $this->assertEquals(new OptionalValueResolver(), $resolver);
        $this->assertEquals(new OptionalValueResolver(new FakeValueResolver(), new FakeValueResolver()), $newResolver);
    }

    public function testCanResolveOptionalValues()
    {
        $resolver = new OptionalValueResolver();

        $this->assertTrue($resolver->canResolve(new OptionalValue('', '')));
        $this->assertFalse($resolver->canResolve(new FakeValue()));
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Resolver\Value\Chainable\OptionalValueResolver::resolve" to be called only if it has a resolver.
     */
    public function testCannotResolveValueIfHasNoResolver()
    {
        $value = new FixturePropertyValue(new FakeValue(), '');
        $resolver = new OptionalValueResolver();
        $resolver->resolve($value, new FakeFixture(), ResolvedFixtureSetFactory::create(), [], new GenerationContext());
    }

    public function testReturnsSetWithResolvedValue()
    {
        //TODO
    }
}
