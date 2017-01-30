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

namespace Nelmio\Alice\Generator\Caller;

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\Object\SimpleObject;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\Definition\Value\FakeObject;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Entity\Caller\Dummy;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Throwable\Exception\RootResolutionException;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\GenerationThrowable;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\Generator\Caller\SimpleCaller
 */
class SimpleCallerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsACaller()
    {
        $this->assertTrue(is_a(SimpleCaller::class, CallerInterface::class, true));
    }

    public function testIsValueResolverAware()
    {
        $this->assertEquals(
            (new SimpleCaller())->withValueResolver(new FakeValueResolver()),
            new SimpleCaller(new FakeValueResolver())
        );
    }

    /**
     * @expectedException \Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\Generator\Caller\SimpleCaller::doCallsOn" to be called only if it has a resolver.
     */
    public function testThrowsAnExceptionIfDoesNotHaveAResolver()
    {
        $obj = new FakeObject();

        $caller = new SimpleCaller();
        $caller->doCallsOn($obj, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testCallsMethodsOntoTheGivenObject()
    {
        $dummyProphecy = $this->prophesize(Dummy::class);
        /** @var Dummy $dummy */
        $dummy = $dummyProphecy->reveal();

        $object = new SimpleObject('dummy', $dummy);
        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtures = (new FixtureBag())->with(
                new SimpleFixture(
                    'dummy',
                    Dummy::class,
                    new SpecificationBag(
                        null,
                        new PropertyBag(),
                        (new MethodCallBag())
                            ->with(new SimpleMethodCall('setTitle', [ 'foo_title' ]))
                            ->with(new SimpleMethodCall('addFoo'))
                            ->with(new SimpleMethodCall('addFoo'))
                    )
                )
            )
        );
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $caller = new SimpleCaller(new FakeValueResolver());
        $caller->doCallsOn($object, $set, $context);

        $dummyProphecy->setTitle('foo_title')->shouldHaveBeenCalled();
        $dummyProphecy->addFoo()->shouldHaveBeenCalledTimes(2);
    }

    public function testResolvesAllPropertyValues()
    {
        $object = new SimpleObject('dummy', new Dummy());
        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtures = (new FixtureBag())->with(
                $fixture = new SimpleFixture(
                    'dummy',
                    Dummy::class,
                    new SpecificationBag(
                        null,
                        new PropertyBag(),
                        (new MethodCallBag())
                            ->with(new SimpleMethodCall('setTitle', ['fake_title']))
                            ->with(new SimpleMethodCall('setTitle', [$titleValue = new FakeValue()]))
                    )
                )
            )
        );
        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $setAfterFirstResolution = ResolvedFixtureSetFactory::create(new ParameterBag(['iteration' => 1]), $fixtures);
        $resolverProphecy
            ->resolve(
                $titleValue,
                $fixture,
                $set,
                [
                    '_instances' => $set->getObjects()->toArray(),
                ],
                $context
            )
            ->willReturn(
                new ResolvedValueWithFixtureSet('Generated Title', $setAfterFirstResolution)
            )
        ;

        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $expected = new ResolvedFixtureSet(
            new ParameterBag(['iteration' => 1]),
            $fixtures,
            new ObjectBag(['dummy' => $object])
        );

        $caller = new SimpleCaller($resolver);
        $actual = $caller->doCallsOn($object, $set, $context);

        $this->assertEquals($expected, $actual);

        $resolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testThrowsAGenerationThrowableIfResolutionFails()
    {
        $object = new SimpleObject('dummy', new Dummy());
        $set = ResolvedFixtureSetFactory::create(
            null,
            $fixtures = (new FixtureBag())->with(
                $fixture = new SimpleFixture(
                    'dummy',
                    Dummy::class,
                    new SpecificationBag(
                        null,
                        new PropertyBag(),
                        (new MethodCallBag())
                            ->with(new SimpleMethodCall('setTitle', [$titleValue = new FakeValue()]))
                    )
                )
            )
        );

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $resolverProphecy
            ->resolve(Argument::cetera())
            ->willThrow(RootResolutionException::class)
        ;
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $caller = new SimpleCaller($resolver);
        try {
            $caller->doCallsOn($object, $set, new GenerationContext());
            $this->fail('Expected exception to be thrown.');
        } catch (GenerationThrowable $throwable) {
            // Expected result
        }
    }
}
