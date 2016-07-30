<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception;

/**
 * @covers Nelmio\Alice\Exception\ObjectNotFoundException
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
    }
}
