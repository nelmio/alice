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

namespace Nelmio\Alice\Throwable\Exception\Generator\ObjectGenerator;

use Nelmio\Alice\Throwable\GenerationThrowable;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\ObjectGenerator\ObjectGeneratorNotFoundException
 */
class ObjectGeneratorNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALogicException()
    {
        $this->assertTrue(is_a(ObjectGeneratorNotFoundException::class, \LogicException::class, true));
    }

    public function testIsNotAGenerationThrowable()
    {
        $this->assertFalse(is_a(ObjectGeneratorNotFoundException::class, GenerationThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = ObjectGeneratorNotFoundException::createUnexpectedCall('dummyMethod');

        $this->assertEquals(
            'Expected method "dummyMethod" to be called only if it has a generator.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();
        $exception = ObjectGeneratorNotFoundException::createUnexpectedCall('dummyMethod', $code, $previous);

        $this->assertEquals(
            'Expected method "dummyMethod" to be called only if it has a generator.',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildObjectGeneratorNotFoundException::createUnexpectedCall('dummyMethod');
        $this->assertInstanceOf(ChildObjectGeneratorNotFoundException::class, $exception);
    }
}

class ChildObjectGeneratorNotFoundException extends ObjectGeneratorNotFoundException
{
}

