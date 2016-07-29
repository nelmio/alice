<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\FixtureBuilder\Denormalizer;

use Nelmio\Alice\Throwable\DenormalizationThrowable;

/**
 * @covers Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\UnexpectedValueException
 */
class UnexpectedValueExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsADenormalizationThrowable()
    {
        $this->assertTrue(is_a(UnexpectedValueException::class, DenormalizationThrowable::class, true));
    }
}
