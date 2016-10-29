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

namespace Nelmio\Alice\Exception\Generator\Instantiator;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Throwable\InstantiationThrowable;

/**
 * @covers \Nelmio\Alice\Exception\Generator\Instantiator\InstantiationException
 */
class InstantiationExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(InstantiationException::class, \RuntimeException::class, true));
    }

    public function testIsAnInstantiationThrowable()
    {
        $this->assertTrue(is_a(InstantiationException::class, InstantiationThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = InstantiationException::create(new DummyFixture('foo'));

        $this->assertEquals(
            'Could not instantiate fixture "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();
        $exception = InstantiationException::create(new DummyFixture('foo'), $code, $previous);

        $this->assertEquals(
            'Could not instantiate fixture "foo".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildInstantiationException::create(new DummyFixture('foo'));
        $this->assertInstanceOf(ChildInstantiationException::class, $exception);
    }
}

class ChildInstantiationException extends InstantiationException
{
}
