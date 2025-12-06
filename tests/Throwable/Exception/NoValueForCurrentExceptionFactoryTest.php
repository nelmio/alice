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

namespace Nelmio\Alice\Throwable\Exception;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(NoValueForCurrentExceptionFactory::class)]
final class NoValueForCurrentExceptionFactoryTest extends TestCase
{
    public function testCreateException(): void
    {
        $exception = NoValueForCurrentExceptionFactory::create(new DummyFixture('dummy'));

        self::assertEquals(
            'No value for \'<current()>\' found for the fixture "dummy".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
