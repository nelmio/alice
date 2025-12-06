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

namespace Nelmio\Alice\Throwable\Exception\Generator\Resolver;

use Error;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\DummyValue;
use Nelmio\Alice\Definition\Value\FixtureMethodCallValue;
use Nelmio\Alice\Definition\Value\FunctionCallValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\NoSuchMethodExceptionFactory
 * @internal
 */
final class NoSuchMethodExceptionFactoryTest extends TestCase
{
    public function testCreateForFixture(): void
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());
        $methodCall = new FixtureMethodCallValue(
            new DummyValue('dummy'),
            new FunctionCallValue('foo'),
        );

        $exception = NoSuchMethodExceptionFactory::createForFixture($fixture, $methodCall);

        self::assertEquals(
            'Could not find the method "foo" of the object "dummy" (class: Dummy).',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());

        $code = 500;
        $previous = new Error();

        $exception = NoSuchMethodExceptionFactory::createForFixture($fixture, $methodCall, $code, $previous);

        self::assertEquals(
            'Could not find the method "foo" of the object "dummy" (class: Dummy).',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
