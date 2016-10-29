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

namespace Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\FlagParser;

use Nelmio\Alice\Throwable\DenormalizationThrowable;

/**
 * @covers \Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\FlagParser\FlagParserNotFoundException
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

    public function testTestCreateNewException()
    {
        $exception = FlagParserNotFoundException::create('foo');
        $this->assertEquals(
            'No suitable flag parser found to handle the element "foo".',
            $exception->getMessage()
        );

        $code = 100;
        $previous = new \Exception();
        $exception = FlagParserNotFoundException::create('foo', $code, $previous);
        $this->assertEquals(
            'No suitable flag parser found to handle the element "foo".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testTestCreateNewExceptionForUnexpectedCall()
    {
        $exception = FlagParserNotFoundException::createUnexpectedCall('foo');
        $this->assertEquals(
            'Expected method "foo" to be called only if it has a flag parser.',
            $exception->getMessage()
        );

        $code = 100;
        $previous = new \Exception();
        $exception = FlagParserNotFoundException::createUnexpectedCall('foo', $code, $previous);
        $this->assertEquals(
            'Expected method "foo" to be called only if it has a flag parser.',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildFlagParserNotFoundException::create('foo');
        $this->assertInstanceOf(ChildFlagParserNotFoundException::class, $exception);

        $exception = ChildFlagParserNotFoundException::createUnexpectedCall('foo');
        $this->assertInstanceOf(ChildFlagParserNotFoundException::class, $exception);
    }
}

class ChildFlagParserNotFoundException extends FlagParserNotFoundException
{
}
