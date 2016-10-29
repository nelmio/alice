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

namespace Nelmio\Alice\Exception\Generator\Resolver;

use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\Throwable\ResolutionThrowable;

/**
 * @covers \Nelmio\Alice\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedException
 */
class UniqueValueGenerationLimitReachedExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsARuntimeException()
    {
        $this->assertTrue(is_a(UniqueValueGenerationLimitReachedException::class, \RuntimeException::class, true));
    }

    public function testIsAResolutionThrowable()
    {
        $this->assertTrue(is_a(UniqueValueGenerationLimitReachedException::class, ResolutionThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = UniqueValueGenerationLimitReachedException::create(
            new UniqueValue('unique_id', new \stdClass()),
            10
        );

        $this->assertEquals(
            'Could not generate a unique value after 10 attempts for "unique_id".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());

        $code = 500;
        $previous = new \Error();
        $exception = UniqueValueGenerationLimitReachedException::create(
            new UniqueValue('unique_id', new \stdClass()),
            10,
            $code,
            $previous
        );

        $this->assertEquals(
            'Could not generate a unique value after 10 attempts for "unique_id".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildUniqueValueGenerationLimitReachedException::create(
            new UniqueValue('unique_id', new \stdClass()),
            10
        );
        $this->assertInstanceOf(ChildUniqueValueGenerationLimitReachedException::class, $exception);
    }
}

class ChildUniqueValueGenerationLimitReachedException extends UniqueValueGenerationLimitReachedException
{
}
