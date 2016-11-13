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

/**
 * @covers \Nelmio\Alice\Throwable\Exception\ObjectNotFoundException
 */
class ObjectNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(ObjectNotFoundException::class, \RuntimeException::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = ObjectNotFoundException::create('foo', 'Dummy');

        $this->assertEquals(
            'Could not find the object "foo" of the class "Dummy".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());

        $code = 500;
        $previous = new \Error();
        $exception = ObjectNotFoundException::create('foo', 'Dummy', $code, $previous);

        $this->assertEquals(
            'Could not find the object "foo" of the class "Dummy".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildObjectNotFoundException::create('foo', 'Dummy');
        $this->assertInstanceOf(ChildObjectNotFoundException::class, $exception);
    }
}

class ChildObjectNotFoundException extends ObjectNotFoundException
{
}
