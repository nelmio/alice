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

namespace Nelmio\Alice\Generator\Instantiator\Chainable;

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\ServiceReference\DummyReference;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Entity\Instantiator\AbstractDummyWithRequiredParameterInConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithRequiredParameterInConstructor;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Instantiator\ChainableInstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @internal
 */
#[CoversClass(NoCallerMethodCallInstantiator::class)]
final class NoCallerMethodCallInstantiatorTest extends TestCase
{
    /**
     * @var NoCallerMethodCallInstantiator
     */
    private $instantiator;

    protected function setUp(): void
    {
        $this->instantiator = new NoCallerMethodCallInstantiator();
    }

    public function testIsAChainableInstantiator(): void
    {
        self::assertTrue(is_a(NoCallerMethodCallInstantiator::class, ChainableInstantiatorInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(NoCallerMethodCallInstantiator::class))->isCloneable());
    }

    public function testCannotInstantiateFixtureWithDefaultConstructor(): void
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());

        self::assertFalse($this->instantiator->canInstantiate($fixture));
    }

    public function testCannotInstantiateFixtureWithNoMethodCallConstructor(): void
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create(new NoMethodCall()));

        self::assertFalse($this->instantiator->canInstantiate($fixture));
    }

    public function testCannotInstantiateFixtureWithIfConstructorIsAFactory(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            'Dummy',
            SpecificationBagFactory::create(new MethodCallWithReference(new DummyReference(), 'fake')),
        );

        self::assertFalse($this->instantiator->canInstantiate($fixture));
    }

    public function testCanInstantiateFixtureWithIfConstructorIsAMalformedFactory(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            'Dummy',
            SpecificationBagFactory::create(new SimpleMethodCall('fake')),
        );

        self::assertTrue($this->instantiator->canInstantiate($fixture));
    }

    public function testInstantiatesObjectWithArguments(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithRequiredParameterInConstructor::class,
            SpecificationBagFactory::create(
                new SimpleMethodCall('__construct', [10]),
            ),
        );
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = new DummyWithRequiredParameterInConstructor(10);
        $actual = $set->getObjects()->get($fixture)->getInstance();

        self::assertEquals($expected, $actual);
    }

    /**
     * Edge case allowed because this scenario should not occur. Indeed if the method is other than the constructor,
     * the constructor is then a factory (static or not) i.e. has a caller. This situation is handled at the
     * denormalization level.
     */
    public function testIgnoresConstructorMethodSpecifiedByTheFixtureIfIsSomethingElseThanTheConstructor(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithRequiredParameterInConstructor::class,
            SpecificationBagFactory::create(
                new SimpleMethodCall('fake', [10]),
            ),
        );
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = new DummyWithRequiredParameterInConstructor(10);
        $actual = $set->getObjects()->get($fixture)->getInstance();

        self::assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionIfCouldNotInstantiateObject(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            AbstractDummyWithRequiredParameterInConstructor::class,
            SpecificationBagFactory::create(
                new SimpleMethodCall('fake', [10]),
            ),
        );

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Could not instantiate fixture "dummy".');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }
}
