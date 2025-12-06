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
use Nelmio\Alice\Definition\Value\DummyValue;
use Nelmio\Alice\Definition\Value\FakeObject;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Entity\Caller\Dummy;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSetFactory;
use Nelmio\Alice\Generator\ResolvedValueWithFixtureSet;
use Nelmio\Alice\Generator\Resolver\Value\FakeValueResolver;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Throwable\Exception\RootResolutionException;
use Nelmio\Alice\Throwable\GenerationThrowable;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use ReflectionClass;
use stdClass;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(SimpleCaller::class)]
final class SimpleCallerTest extends TestCase
{
    use ProphecyTrait;

    public function testIsNotClonable(): void
    {
        self::assertFalse((new ReflectionClass(SimpleCaller::class))->isCloneable());
    }

    public function testIsACaller(): void
    {
        self::assertTrue(is_a(SimpleCaller::class, CallerInterface::class, true));
    }

    public function testIsValueResolverAware(): void
    {
        self::assertEquals(
            (
                new SimpleCaller(
                    new FakeCallProcessor(),
                )
            )->withValueResolver(new FakeValueResolver()),
            new SimpleCaller(
                new FakeCallProcessor(),
                new FakeValueResolver(),
            ),
        );
    }

    public function testThrowsAnExceptionIfDoesNotHaveAResolver(): void
    {
        $obj = new FakeObject();

        $caller = new SimpleCaller(
            new FakeCallProcessor(),
        );

        $this->expectException(ResolverNotFoundException::class);
        $this->expectExceptionMessage('Expected method "Nelmio\Alice\Generator\Caller\SimpleCaller::doCallsOn" to be called only if it has a resolver.');

        $caller->doCallsOn($obj, ResolvedFixtureSetFactory::create(), new GenerationContext());
    }

    public function testCallsMethodsOntoTheGivenObject(): void
    {
        $object = new SimpleObject('dummy', new stdClass());

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
                            ->with($methodCall1 = new SimpleMethodCall('setTitle', ['foo_title']))
                            ->with($methodCall2 = new SimpleMethodCall('addFoo'))
                            ->with($methodCall3 = new SimpleMethodCall('addFoo')),
                    ),
                ),
            ),
        );

        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $callProcessorProphecy = $this->prophesize(CallProcessorInterface::class);
        $callProcessorProphecy
            ->process(
                $object,
                $set,
                $context,
                $methodCall1,
            )
            ->willReturn(
                $set1 = ResolvedFixtureSetFactory::create(
                    new ParameterBag([
                        'pass' => 1,
                    ]),
                    $fixtures,
                ),
            );
        $callProcessorProphecy
            ->process(
                $object,
                $set1,
                $context,
                $methodCall2->withArguments([]),
            )
            ->willReturn(
                $set2 = ResolvedFixtureSetFactory::create(
                    new ParameterBag([
                        'pass' => 2,
                    ]),
                    $fixtures,
                ),
            );
        $callProcessorProphecy
            ->process(
                $object,
                $set2,
                $context,
                $methodCall3->withArguments([]),
            )
            ->willReturn(
                $set3 = ResolvedFixtureSetFactory::create(
                    new ParameterBag([
                        'pass' => 3,
                    ]),
                    $fixtures,
                ),
            );
        /** @var CallProcessorInterface $callProcessor */
        $callProcessor = $callProcessorProphecy->reveal();

        $caller = new SimpleCaller(
            $callProcessor,
            new FakeValueResolver(),
        );
        $caller->doCallsOn($object, $set, $context);

        $callProcessorProphecy->process(Argument::cetera())->shouldHaveBeenCalledTimes(3);
    }

    public function testResolvesAllPropertyValues(): void
    {
        $object = new SimpleObject('dummy', new Dummy());

        $originalSet = ResolvedFixtureSetFactory::create(
            null,
            $fixtures = (new FixtureBag())->with(
                $fixture = new SimpleFixture(
                    'dummy',
                    Dummy::class,
                    new SpecificationBag(
                        null,
                        new PropertyBag(),
                        (new MethodCallBag())
                            ->with($methodCall1 = new SimpleMethodCall('setTitle', [$value1 = new DummyValue('val1')]))
                            ->with($methodCall2 = new SimpleMethodCall('setTitle', [$value2 = new DummyValue('val2')]))
                            ->with($methodCall3 = new SimpleMethodCall('setTitle', ['fake_title'])),
                    ),
                ),
            ),
        );

        $context = new GenerationContext();
        $context->markIsResolvingFixture('foo');

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $callProcessorProphecy = $this->prophesize(CallProcessorInterface::class);
        /** @var CallProcessorInterface $callProcessor */
        $callProcessor = $callProcessorProphecy->reveal();

        $resolverProphecy
            ->resolve(
                $value1,
                $fixture,
                $originalSet,
                [
                    '_instances' => [],
                ],
                $context,
            )
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    'Generated Title 1',
                    $setAfterResolution1 = ResolvedFixtureSetFactory::create(
                        new ParameterBag([
                            'resolution pass' => 1,
                        ]),
                        $fixtures,
                    ),
                ),
            );

        $methodCall1AfterResolution = new SimpleMethodCall(
            'setTitle',
            ['Generated Title 1'],
        );

        $callProcessorProphecy
            ->process(
                $object,
                $setAfterResolution1,
                $context,
                $methodCall1AfterResolution,
            )
            ->willReturn(
                $setAfterProcessing1 = ResolvedFixtureSetFactory::create(
                    new ParameterBag([
                        'resolution pass' => 1,
                        'processing pass' => 1,
                    ]),
                    $fixtures,
                    new ObjectBag($objectsAfterProcessing1 = [
                        'dummy' => new SimpleObject('dummy', $dummy = new stdClass()),
                    ]),
                ),
            );

        $resolverProphecy
            ->resolve(
                $value2,
                $fixture,
                $setAfterProcessing1,
                [
                    '_instances' => [
                        'dummy' => $dummy,
                    ],
                ],
                $context,
            )
            ->willReturn(
                new ResolvedValueWithFixtureSet(
                    'Generated Title 2',
                    $setAfterResolution2 = ResolvedFixtureSetFactory::create(
                        new ParameterBag([
                            'resolution pass' => 2,
                            'processing pass' => 1,
                        ]),
                        $fixtures,
                        new ObjectBag($objectsAfterProcessing1),
                    ),
                ),
            );

        $methodCall2AfterResolution = new SimpleMethodCall(
            'setTitle',
            ['Generated Title 2'],
        );

        $callProcessorProphecy
            ->process(
                $object,
                $setAfterResolution2,
                $context,
                $methodCall2AfterResolution,
            )
            ->willReturn(
                $setAfterProcessing2 = ResolvedFixtureSetFactory::create(
                    new ParameterBag([
                        'resolution pass' => 2,
                        'processing pass' => 2,
                    ]),
                    $fixtures,
                    new ObjectBag($objectsAfterProcessing1),
                ),
            );
        $callProcessorProphecy
            ->process(
                $object,
                $setAfterProcessing2,
                $context,
                $methodCall3,
            )
            ->willReturn(
                $setAfterProcessing3 = ResolvedFixtureSetFactory::create(
                    new ParameterBag([
                        'resolution pass' => 3,
                        'processing pass' => 2,
                    ]),
                    $fixtures,
                    new ObjectBag($objectsAfterProcessing1),
                ),
            );

        $expected = $setAfterProcessing3;

        $caller = new SimpleCaller($callProcessor, $resolver);
        $actual = $caller->doCallsOn($object, $originalSet, $context);

        self::assertSame($expected, $actual);

        $resolverProphecy->resolve(Argument::cetera())->shouldHaveBeenCalledTimes(2);
        $callProcessorProphecy->process(Argument::cetera())->shouldHaveBeenCalledTimes(3);
    }

    public function testThrowsAGenerationThrowableIfResolutionFails(): void
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
                            ->with(new SimpleMethodCall('setTitle', [new FakeValue()])),
                    ),
                ),
            ),
        );

        $resolverProphecy = $this->prophesize(ValueResolverInterface::class);
        $resolverProphecy
            ->resolve(Argument::cetera())
            ->willThrow(RootResolutionException::class);
        /** @var ValueResolverInterface $resolver */
        $resolver = $resolverProphecy->reveal();

        $caller = new SimpleCaller(new FakeCallProcessor(), $resolver);

        try {
            $caller->doCallsOn($object, $set, new GenerationContext());

            self::fail('Expected exception to be thrown.');
        } catch (GenerationThrowable) {
            // Expected result
        }
    }
}
