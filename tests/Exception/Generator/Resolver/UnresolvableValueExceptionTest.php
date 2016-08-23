<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\Resolver;

use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * @covers Nelmio\Alice\Exception\Generator\Resolver\UnresolvableValueException
 */
class UnresolvableValueExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(UnresolvableValueException::class, \RuntimeException::class, true));
    }

    public function testIsAResolutionThrowable()
    {
        $this->assertTrue(is_a(UnresolvableValueException::class, ResolutionThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = UnresolvableValueException::create(new FakeValue());

        $this->assertEquals(
            'Could not resolve value Nelmio\Alice\Definition\Value\FakeValue.',
            $exception->getMessage()
        );
    }
}
