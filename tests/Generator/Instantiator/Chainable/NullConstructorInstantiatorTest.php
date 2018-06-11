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
    public function setUp()
    {
        $this->instantiator = new NullConstructorInstantiator();
    }

    public function testIsAChainableInstantiator()
    {
        $this->assertTrue(is_a(NullConstructorInstantiator::class, ChainableInstantiatorInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(NullConstructorInstantiator::class))->isCloneable());
    }

    public function testCanInstantiateFixtureUsingADefaultConstructor()
    {
        $fixture = new SimpleFixture('dummy', 'Nelmio\Alice\Entity\User', SpecificationBagFactory::create());

        $this->assertTrue($this->instantiator->canInstantiate($fixture));
    }

    public function testIfCannotGetConstructorReflectionTriesToInstantiateObjectWithoutArguments()
    {
        $fixture = new SimpleFixture('dummy', DummyWithDefaultConstructor::class, SpecificationBagFactory::create());
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $expected = new DummyWithDefaultConstructor();
        $actual = $set->getObjects()->get($fixture)->getInstance();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Could not instantiate fixture "dummy".
     */
    public function testThrowsAnExceptionIfInstantiatingObjectWithoutArgumentsFails()
    {
        $fixture = new SimpleFixture('dummy', AbstractDummy::class, SpecificationBagFactory::create());
        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testThrowsAnExceptionIfReflectionFailsWithAnotherErrorThanMethodNotExisting()
    {
        try {
            $fixture = new SimpleFixture('dummy', 'Unknown', SpecificationBagFactory::create());
            $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

            $this->fail('Expected exception to be thrown.');
        } catch (InstantiationException $exception) {
            $this->assertEquals(
                'Could not get the necessary data on the constructor to instantiate "dummy".',
                $exception->getMessage()
            );
            $this->assertEquals(0, $exception->getCode());
            $this->assertNotNull($exception->getPrevious());
        }
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Could not instantiate "dummy", the constructor has mandatory parameters but no parameters have been given.
     */
    public function testThrowsAnExceptionIfObjectConstructorHasMandatoryParameters()
    {
        $fixture = new SimpleFixture('dummy', DummyWithRequiredParameterInConstructor::class, SpecificationBagFactory::create());
        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Could not instantiate fixture "dummy".
     */
    public function testThrowsAnExceptionIfObjectInstantiationFailsUnderNominalConditions()
    {
        $fixture = new SimpleFixture('dummy', DummyWithExplicitDefaultConstructorThrowingException::class, SpecificationBagFactory::create());
        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Could not instantiate "dummy", the constructor of "Nelmio\Alice\Entity\Instantiator\DummyWithPrivateConstructor" is not public.
     */
    public function testThrowsAnExceptionIfObjectConstructorIsPrivate()
    {
        $fixture = new SimpleFixture('dummy', DummyWithPrivateConstructor::class, SpecificationBagFactory::create());
        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiationException
     * @expectedExceptionMessage Could not instantiate "dummy", the constructor of "Nelmio\Alice\Entity\Instantiator\DummyWithProtectedConstructor" is not public.
     */
    public function testThrowsAnExceptionIfObjectConstructorIsProtected()
    {
        $fixture = new SimpleFixture('dummy', DummyWithProtectedConstructor::class, SpecificationBagFactory::create());
        $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }
}
