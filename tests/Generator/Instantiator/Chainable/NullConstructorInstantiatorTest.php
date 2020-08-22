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
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Entity\Instantiator\AbstractDummy;
use Nelmio\Alice\Entity\Instantiator\DummyWithDefaultConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithExplicitDefaultConstructorThrowingException;
use Nelmio\Alice\Entity\Instantiator\DummyWithPrivateConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithProtectedConstructor;
use Nelmio\Alice\Entity\Instantiator\DummyWithRequiredParameterInConstructor;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\Instantiator\ChainableInstantiatorInterface;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Instantiator\Chainable\NullConstructorInstantiator
 */
class NullConstructorInstantiatorTest extends TestCase
{
    /**
     * @var NullConstructorInstantiator
     */
    private $instantiator;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->instantiator = new NullConstructorInstantiator();
    }

    public function testIsAChainableInstantiator(): void
    {
        static::assertTrue(is_a(NullConstructorInstantiator::class, ChainableInstantiatorInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        static::assertFalse((new ReflectionClass(NullConstructorInstantiator::class))->isCloneable());
    }

    public function testCanInstantiateFixtureUsingADefaultConstructor(): void
    {
        $fixture = new SimpleFixture('dummy', 'Nelmio\Alice\Entity\User', SpecificationBagFactory::create());

        static::assertTrue($this->instantiator->canInstantiate($fixture));
    }

    public function testIfCannotGetConstructorReflectionTriesToInstantiateObjectWithoutArguments(): void
    {
        $fixture = new SimpleFixture('dummy', DummyWithDefaultConstructor::class, SpecificationBagFactory::create());
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = new DummyWithDefaultConstructor();
        $actual = $set->getObjects()->get($fixture)->getInstance();

        static::assertEquals($expected, $actual);
    }

    public function testThrowsAnExceptionIfInstantiatingObjectWithoutArgumentsFails(): void
    {
        $fixture = new SimpleFixture('dummy', AbstractDummy::class, SpecificationBagFactory::create());

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Could not instantiate fixture "dummy".');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testThrowsAnExceptionIfReflectionFailsWithAnotherErrorThanMethodNotExisting(): void
    {
        try {
            $fixture = new SimpleFixture('dummy', 'Unknown', SpecificationBagFactory::create());
            $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

            static::fail('Expected exception to be thrown.');
        } catch (InstantiationException $exception) {
            static::assertEquals(
                'Could not get the necessary data on the constructor to instantiate "dummy".',
                $exception->getMessage()
            );
            static::assertEquals(0, $exception->getCode());
            static::assertNotNull($exception->getPrevious());
        }
    }

    public function testThrowsAnExceptionIfObjectConstructorHasMandatoryParameters(): void
    {
        $fixture = new SimpleFixture('dummy', DummyWithRequiredParameterInConstructor::class, SpecificationBagFactory::create());

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Could not instantiate "dummy", the constructor has mandatory parameters but no parameters have been given.');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testThrowsAnExceptionIfObjectInstantiationFailsUnderNominalConditions(): void
    {
        $fixture = new SimpleFixture('dummy', DummyWithExplicitDefaultConstructorThrowingException::class, SpecificationBagFactory::create());

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Could not instantiate fixture "dummy".');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testThrowsAnExceptionIfObjectConstructorIsPrivate(): void
    {
        $fixture = new SimpleFixture('dummy', DummyWithPrivateConstructor::class, SpecificationBagFactory::create());

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Could not instantiate "dummy", the constructor of "Nelmio\Alice\Entity\Instantiator\DummyWithPrivateConstructor" is not public.');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testThrowsAnExceptionIfObjectConstructorIsProtected(): void
    {
        $fixture = new SimpleFixture('dummy', DummyWithProtectedConstructor::class, SpecificationBagFactory::create());

        $this->expectException(InstantiationException::class);
        $this->expectExceptionMessage('Could not instantiate "dummy", the constructor of "Nelmio\Alice\Entity\Instantiator\DummyWithProtectedConstructor" is not public.');

        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }
}
