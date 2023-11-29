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

use Nelmio\Alice\Definition\Value\UniqueValue;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedExceptionFactory
 * @internal
 */
class UniqueValueGenerationLimitReachedExceptionFactoryTest extends TestCase
{
    public function testCreateNewExceptionWithFactory(): void
    {
        $exception = UniqueValueGenerationLimitReachedExceptionFactory::create(
            new UniqueValue('unique_id', new stdClass()),
            10,
        );

        self::assertEquals(
            'Could not generate a unique value after 10 attempts for "unique_id".',
            $exception->getMessage(),
        );
        self::assertEquals(0, $exception->getCode());
        self::assertNull($exception->getPrevious());
    }
}
