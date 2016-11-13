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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer;

use Nelmio\Alice\Throwable\DenormalizationThrowable;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerNotFoundException
 */
class DenormalizerNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testIsALogicException()
    {
        $this->assertTrue(is_a(DenormalizerNotFoundException::class, \LogicException::class, true));
    }

    public function testIsNotADenormalizationThrowable()
    {
        $this->assertFalse(is_a(DenormalizerNotFoundException::class, DenormalizationThrowable::class, true));
    }

    public function testTestCreateNewExceptionWithFactoryForFixture()
    {
        $exception = DenormalizerNotFoundException::createForFixture('foo');

        $this->assertEquals(
            'No suitable fixture denormalizer found to handle the fixture with the reference "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
        
        
        $code = 500;
        $previous = new \Error('hello');

        $exception = DenormalizerNotFoundException::createForFixture('foo', $code, $previous);
        $this->assertEquals(
            'No suitable fixture denormalizer found to handle the fixture with the reference "foo".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testTestCreateNewExceptionWithFactoryForUnexpectedCall()
    {
        $exception = DenormalizerNotFoundException::createUnexpectedCall('fake');

        $this->assertEquals(
            'Expected method "fake" to be called only if it has a denormalizer.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error('hello');

        $exception = DenormalizerNotFoundException::createUnexpectedCall('fake', $code, $previous);
        $this->assertEquals(
            'Expected method "fake" to be called only if it has a denormalizer.',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testIsExtensible()
    {
        $exception = ChildDenormalizerNotFoundException::createForFixture('foo');
        $this->assertInstanceOf(ChildDenormalizerNotFoundException::class, $exception);

        $exception = ChildDenormalizerNotFoundException::createUnexpectedCall('fake');
        $this->assertInstanceOf(ChildDenormalizerNotFoundException::class, $exception);
    }
}

class ChildDenormalizerNotFoundException extends DenormalizerNotFoundException
{
}
