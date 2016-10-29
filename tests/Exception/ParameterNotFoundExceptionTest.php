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

namespace Nelmio\Alice\Exception;

/**
 * @covers \Nelmio\Alice\Exception\ParameterNotFoundException
 */
class ParameterNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(ParameterNotFoundException::class, \RuntimeException::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = ParameterNotFoundException::create('foo');

        $this->assertEquals(
            'Could not find the parameter "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();
        $exception = ParameterNotFoundException::create('foo', $code, $previous);

        $this->assertEquals(
            'Could not find the parameter "foo".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildParameterNotFoundException::create('foo');
        $this->assertInstanceOf(ChildParameterNotFoundException::class, $exception);
    }
}

class ChildParameterNotFoundException extends ParameterNotFoundException
{
}
