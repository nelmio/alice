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
use Nelmio\Alice\Definition\ServiceReference\DummyReference;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Entity\Instantiator\AbstractDummyWithRequiredParameterInConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithExplicitDefaultConstructorThrowingException;
use Nelmio\Alice\Entity\Instantiator\DummyWithFakeNamedConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithNamedConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithNamedConstructorAndOptionalParameters;
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
#[CoversClass(StaticFactoryInstantiator::class)]
final class StaticFactoryInstantiatorTest extends TestCase
{
    /**
     * @var StaticFactoryInstantiator
     */
    private $instantiator;

    protected function setUp(): void
    {
        $this->instantiator = new StaticFactoryInstantiator();
    }

    public function testIsAChainableInstantiator(): void
    {
        self::assertTrue(is_a(StaticFactoryInstantiator::class, ChainableInstantiatorInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(StaticFactoryInstantiator::class))->isCloneable());
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

    public function testCannotInstantiateFixtureWithIfConstructorIsANonStaticFactory(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            'Dummy',
            SpecificationBagFactory::create(new MethodCallWithReference(new DummyReference(), 'fake')),
        );

        self::assertFalse($this->instantiator->canInstantiate($fixture));
    }

    public function testCanInstantiateFixtureWithIfConstructorIsAStaticFactory(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            'Dummy',
            SpecificationBagFactory::create(new MethodCallWithReference(new StaticReference('static_reference'), 'fake')),
        );

        self::assertTrue($this->instantiator->canInstantiate($fixture));
    }

    public function testInstantiatesObjectWithFactory(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithNamedConstructor::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithNamedConstructor::class),
                    'namedConstruct',
                ),
            ),
        );
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = DummyWithNamedConstructor::namedConstruct();
        $actual = $set->getObjects()->get($fixture)->getInstance();

        self::assertEquals($expected, $actual);
    }

    public function testInstantiatesObjectWithFactoryAndArguments(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithNamedConstructorAndOptionalParameters::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithNamedConstructorAndOptionalParameters::class),
                    'namedConstruct',
                    [10],
                ),
            ),
        );
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = DummyWithNamedConstructorAndOptionalParameters::namedConstruct(10);
        $actual = $set->getObjects()->get($fixture)->getInstance();

        self::assertEquals($expected, $actual);
    }

    public function testInstantiatesObjectWithFactoryAndNamedArguments(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithNamedConstructorAndOptionalParameters::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithNamedConstructorAndOptionalParameters::class),
                    'namedConstruct',
                    ['param' => 10],
                ),
            ),
        );
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = DummyWithNamedConstructorAndOptionalParameters::namedConstruct(10);
        $actual = $set->getObjects()->get($fixture)->getInstance();

        self::assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionIfCouldNotInstantiateObject(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithExplicitDefaultConstructorThrowingException::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithExplicitDefaultConstructorThrowingException::class),
                    'namedConstruct',
                ),
            ),
        );

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Could not instantiate fixture "dummy".');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testThrowsAnExceptionIfCouldNotFindFactoryMethod(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithExplicitDefaultConstructorThrowingException::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithExplicitDefaultConstructorThrowingException::class),
                    'unknownMethod',
                ),
            ),
        );

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Could not instantiate fixture "dummy".');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testThrowsAnExceptionIfCouldNotFindFactoryClass(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithExplicitDefaultConstructorThrowingException::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference('Unknown'),
                    'namedConstruct',
                ),
            ),
        );

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Could not instantiate fixture "dummy".');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testThrowsAnExceptionIfCouldNotCallOnTheFactory(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithExplicitDefaultConstructorThrowingException::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(AbstractDummyWithRequiredParameterInConstructor::class),
                    'namedConstruct',
                    [10],
                ),
            ),
        );

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Could not instantiate fixture "dummy".');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testThrowsAnExceptionIfFixtureClassDoesNotMatchObjectClass(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            'Dummy',
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithNamedConstructorAndOptionalParameters::class),
                    'namedConstruct',
                    [10],
                ),
            ),
        );

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Instantiated fixture was expected to be an instance of "Dummy". Got "Nelmio\Alice\Entity\Instantiator\DummyWithNamedConstructorAndOptionalParameters" instead.');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testThrowsAnExceptionIfFactoryDoesNotReturnAnInstance(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithFakeNamedConstructor::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithFakeNamedConstructor::class),
                    'namedConstruct',
                ),
            ),
        );

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Instantiated fixture was expected to be an instance of "Nelmio\Alice\Entity\Instantiator\DummyWithFakeNamedConstructor". Got "null" instead.');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }
}
