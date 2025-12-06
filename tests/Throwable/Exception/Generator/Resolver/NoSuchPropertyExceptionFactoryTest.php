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
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixturePropertyValue;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(NoSuchPropertyExceptionFactory::class)]
final class NoSuchPropertyExceptionFactoryTest extends TestCase
{
    public function testCreateForFixture(): void
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());
        $property = new FixturePropertyValue(new FakeValue(), 'foo');

        $exception = NoSuchPropertyExceptionFactory::createForFixture($fixture, $property);

        self::assertEquals(
            'Could not find the property "foo" of the object "dummy" (class: Dummy).',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());

        $code = 500;
        $previous = new Error();

        $exception = NoSuchPropertyExceptionFactory::createForFixture($fixture, $property, $code, $previous);

        self::assertEquals(
            'Could not find the property "foo" of the object "dummy" (class: Dummy).',
            $exception->getMessage(),
        );
        self::assertEquals($code, $exception->getCode());
        self::assertSame($previous, $exception->getPrevious());
    }
}
