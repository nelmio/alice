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
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Instantiator\Chainable\StaticFactoryInstantiator
 */
class StaticFactoryInstantiatorTest extends TestCase
{
    /**
     * @var StaticFactoryInstantiator
     */
    private $instantiator;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->instantiator = new StaticFactoryInstantiator();
    }

    public function testIsAChainableInstantiator()
    {
        $this->assertTrue(is_a(StaticFactoryInstantiator::class, ChainableInstantiatorInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(StaticFactoryInstantiator::class))->isCloneable());
    }

    public function testCannotInstantiateFixtureWithDefaultConstructor()
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());

        $this->assertFalse($this->instantiator->canInstantiate($fixture));
    }

    public function testCannotInstantiateFixtureWithNoMethodCallConstructor()
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create(new NoMethodCall()));

        $this->assertFalse($this->instantiator->canInstantiate($fixture));
    }

    public function testCannotInstantiateFixtureWithIfConstructorIsANonStaticFactory()
    {
        $fixture = new SimpleFixture(
            'dummy',
            'Dummy',
            SpecificationBagFactory::create(new MethodCallWithReference(new DummyReference(), 'fake'))
        );

        $this->assertFalse($this->instantiator->canInstantiate($fixture));
    }

    public function testCanInstantiateFixtureWithIfConstructorIsAStaticFactory()
    {
        $fixture = new SimpleFixture(
            'dummy',
            'Dummy',
            SpecificationBagFactory::create(new MethodCallWithReference(new StaticReference('static_reference'), 'fake'))
        );

        $this->assertTrue($this->instantiator->canInstantiate($fixture));
    }

    public function testInstantiatesObjectWithFactory()
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithNamedConstructor::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithNamedConstructor::class),
                    'namedConstruct'
                )
            )
        );
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = DummyWithNamedConstructor::namedConstruct();
        $actual = $set->getObjects()->get($fixture)->getInstance();

        $this->assertEquals($expected, $actual);
    }

    public function testInstantiatesObjectWithFactoryAndArguments()
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithNamedConstructorAndOptionalParameters::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithNamedConstructorAndOptionalParameters::class),
                    'namedConstruct',
                    [10]
                )
            )
        );
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = DummyWithNamedConstructorAndOptionalParameters::namedConstruct(10);
        $actual = $set->getObjects()->get($fixture)->getInstance();

        $this->assertEquals($expected, $actual);
    }

    public function testInstantiatesObjectWithFactoryAndNamedArguments()
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithNamedConstructorAndOptionalParameters::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithNamedConstructorAndOptionalParameters::class),
                    'namedConstruct',
                    ['param' => 10]
                )
            )
        );
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = DummyWithNamedConstructorAndOptionalParameters::namedConstruct(10);
        $actual = $set->getObjects()->get($fixture)->getInstance();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Could not instantiate fixture "dummy".
     */
    public function testThrowsAnExceptionIfCouldNotInstantiateObject()
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithExplicitDefaultConstructorThrowingException::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithExplicitDefaultConstructorThrowingException::class),
                    'namedConstruct'
                )
            )
        );

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Could not instantiate fixture "dummy".
     */
    public function testThrowsAnExceptionIfCouldNotFindFactoryMethod()
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithExplicitDefaultConstructorThrowingException::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithExplicitDefaultConstructorThrowingException::class),
                    'unknownMethod'
                )
            )
        );

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Could not instantiate fixture "dummy".
     */
    public function testThrowsAnExceptionIfCouldNotFindFactoryClass()
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithExplicitDefaultConstructorThrowingException::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference('Unknown'),
                    'namedConstruct'
                )
            )
        );

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Could not instantiate fixture "dummy".
     */
    public function testThrowsAnExceptionIfCouldNotCallOnTheFactory()
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithExplicitDefaultConstructorThrowingException::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(AbstractDummyWithRequiredParameterInConstructor::class),
                    'namedConstruct',
                    [10]
                )
            )
        );

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Instantiated fixture was expected to be an instance of "Dummy". Got "Nelmio\Alice\Entity\Instantiator\DummyWithNamedConstructorAndOptionalParameters" instead.
     */
    public function testThrowsAnExceptionIfFixtureClassDoesNotMatchObjectClass()
    {
        $fixture = new SimpleFixture(
            'dummy',
            'Dummy',
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithNamedConstructorAndOptionalParameters::class),
                    'namedConstruct',
                    [10]
                )
            )
        );
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = DummyWithNamedConstructorAndOptionalParameters::namedConstruct(10);
        $actual = $set->getObjects()->get($fixture)->getInstance();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Instantiated fixture was expected to be an instance of "Nelmio\Alice\Entity\Instantiator\DummyWithFakeNamedConstructor". Got "null" instead.
     */
    public function testThrowsAnExceptionIfFactoryDoesNotReturnAnInstance()
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithFakeNamedConstructor::class,
            SpecificationBagFactory::create(
                new MethodCallWithReference(
                    new StaticReference(DummyWithFakeNamedConstructor::class),
                    'namedConstruct'
                )
            )
        );

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }
}
