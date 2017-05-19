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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser;

use Nelmio\Alice\Throwable\DenormalizationThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException
 */
class FlagParserNotFoundExceptionTest extends TestCase
{
    public function testIsALogicException()
    {
        $this->assertTrue(is_a(FlagParserNotFoundException::class, \LogicException::class, true));
    }

    public function testIsNotADenormalizationThrowable()
    {
        $this->assertFalse(is_a(FlagParserNotFoundException::class, DenormalizationThrowable::class, true));
    }

    public function testIsExtensible()
    {
        $exception = new ChildFlagParserNotFoundException();
        $this->assertInstanceOf(ChildFlagParserNotFoundException::class, $exception);
    }
}
