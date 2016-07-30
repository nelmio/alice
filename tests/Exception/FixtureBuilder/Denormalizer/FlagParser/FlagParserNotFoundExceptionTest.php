<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\FlagParser;

use Nelmio\Alice\Throwable\DenormalizationThrowable;

/**
 * @covers Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException
 */
class FlagParserNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALogicException()
    {
        $this->assertTrue(is_a(FlagParserNotFoundException::class, \LogicException::class, true));
    }

    public function testIsNotADenormalizationThrowable()
    {
        $this->assertFalse(is_a(FlagParserNotFoundException::class, DenormalizationThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = FlagParserNotFoundException::create('foo');

        $this->assertEquals(
            'No suitable flag parser found to handle the element "foo".',
            $exception->getMessage()
        );
    }
}
