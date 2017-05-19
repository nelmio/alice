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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage;

use Nelmio\Alice\Throwable\ExpressionLanguageParseThrowable;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\ExpressionLanguage\ParseException
 */
class ParseExceptionTest extends TestCase
{
    public function testIsAnException()
    {
        $this->assertTrue(is_a(ParseException::class, \Exception::class, true));
    }

    public function testIsAParseThrowable()
    {
        $this->assertTrue(is_a(ParseException::class, ExpressionLanguageParseThrowable::class, true));
    }

    public function testIsExtensible()
    {
        $exception = new ChildParseException();
        $this->assertInstanceOf(ChildParseException::class, $exception);
    }
}
