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
 * @covers \Nelmio\Alice\Exception\Generator\Instantiator\InstantiatorNotFoundException
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
    }
}
