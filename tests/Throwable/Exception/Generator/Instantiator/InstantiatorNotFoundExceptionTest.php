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

namespace Nelmio\Alice\Throwable\Exception\Generator\Instantiator;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Throwable\InstantiationThrowable;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Instantiator\InstantiatorNotFoundException
 */
class InstantiatorNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALogicException()
    {
        $this->assertTrue(is_a(InstantiatorNotFoundException::class, \LogicException::class, true));
    }

    public function testIsNotAnInstantiationThrowable()
    {
        $this->assertFalse(is_a(InstantiatorNotFoundException::class, InstantiationThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = InstantiatorNotFoundException::create(new DummyFixture('foo'));

        $this->assertEquals(
            'No suitable instantiator found for the fixture "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();
        $exception = InstantiatorNotFoundException::create(new DummyFixture('foo'), $code, $previous);

        $this->assertEquals(
            'No suitable instantiator found for the fixture "foo".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildInstantiatorNotFoundException::create(new DummyFixture('foo'));
        $this->assertInstanceOf(ChildInstantiatorNotFoundException::class, $exception);
    }
}

class ChildInstantiatorNotFoundException extends InstantiatorNotFoundException
{
}
