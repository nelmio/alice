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

use PHPUnit\Framework\TestCase;
use Nelmio\Alice\Parameter;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\ParameterNotFoundExceptionFactory
 */
class ParameterNotFoundExceptionFactoryTest extends TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(ParameterNotFoundException::class, \RuntimeException::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = ParameterNotFoundExceptionFactory::create('foo');

        $this->assertEquals(
            'Could not find the parameter "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForWhenResolvingParameter()
    {
        $exception = ParameterNotFoundExceptionFactory::createForWhenResolvingParameter(
            'foo',
            new Parameter('bar', 'baz')
        );

        $this->assertEquals(
            'Could not find the parameter "foo" when resolving "bar".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
