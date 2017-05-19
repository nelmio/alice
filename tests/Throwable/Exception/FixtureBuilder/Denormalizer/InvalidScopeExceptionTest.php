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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer;

use Nelmio\Alice\Throwable\DenormalizationThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\InvalidScopeException
 */
class InvalidScopeExceptionTest extends TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(InvalidScopeException::class, \RuntimeException::class, true));
    }

    public function testIsADenormalizationThrowable()
    {
        $this->assertTrue(is_a(InvalidScopeException::class, DenormalizationThrowable::class, true));
    }

    public function testIsExtensible()
    {
        $exception = new ChildInvalidScopeException();
        $this->assertInstanceOf(ChildInvalidScopeException::class, $exception);
    }
}
