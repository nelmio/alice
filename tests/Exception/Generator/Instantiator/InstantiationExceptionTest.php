<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\Instantiator;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\Throwable\InstantiationThrowable;

/**
 * @covers Nelmio\Alice\Exception\Generator\Instantiator\InstantiationException
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
        $exception0 = InstantiationException::create(new DummyFixture('foo'));
        $exception1 = InstantiationException::create(new DummyFixture('foo'), $previous = new \Exception());

        $this->assertEquals(
            'Could no instantiate fixture "foo".',
            $exception0->getMessage()
        );
        $this->assertNull($exception0->getPrevious());
        $this->assertEquals(
            'Could no instantiate fixture "foo".',
            $exception1->getMessage()
        );
        $this->assertSame($previous, $exception1->getPrevious());
    }
}
