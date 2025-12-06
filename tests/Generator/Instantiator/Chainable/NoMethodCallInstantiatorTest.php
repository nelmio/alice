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
use Nelmio\Alice\Definition\MethodCall\NoMethodCall;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
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
use ReflectionException;
use ReflectionObject;

/**
 * @internal
 */
#[CoversClass(NoMethodCallInstantiator::class)]
final class NoMethodCallInstantiatorTest extends TestCase
{
    /**
     * @var NoMethodCallInstantiator
     */
    private $instantiator;

    protected function setUp(): void
    {
        $this->instantiator = new NoMethodCallInstantiator();
    }

    public function testIsAChainableInstantiator(): void
    {
        self::assertTrue(is_a(NoMethodCallInstantiator::class, ChainableInstantiatorInterface::class, true));
    }

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(NoMethodCallInstantiator::class))->isCloneable());
    }

    public function testCanInstantiateFixtureWithNoMethodCallConstructor(): void
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create(new NoMethodCall()));

        self::assertTrue($this->instantiator->canInstantiate($fixture));
    }

    public function testCannotInstantiateFixtureWithDefaultConstructor(): void
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());

        self::assertFalse($this->instantiator->canInstantiate($fixture));
    }

    public function testInstantiatesWithReflectionAndNoArguments(): void
    {
        $fixture = new SimpleFixture(
            'dummy',
            DummyWithRequiredParameterInConstructor::class,
            SpecificationBagFactory::create(),
        );
        $set = $this->instantiator->instantiate($fixture, ResolvedFixtureSetFactory::create(), new GenerationContext());

        $instance = $set->getObjects()->get($fixture)->getInstance();
        self::assertInstanceOf(DummyWithRequiredParameterInConstructor::class, $instance);

        try {
            (new ReflectionObject($instance))->getProperty('requiredParam');
            self::fail('Expected exception to be thrown.');
        } catch (ReflectionException $exception) {
            self::assertEquals(
                'Property Nelmio\Alice\Entity\Instantiator\DummyWithRequiredParameterInConstructor::$requiredParam'
                .' does not exist',
                $exception->getMessage(),
            );
        }
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
