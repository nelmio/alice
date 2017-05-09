<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerExceptionFactory
 */
class DenormalizerExceptionFactoryTest extends TestCase
{
    public function testTestCreateForUndenormalizableConstructor()
    {
        $exception = DenormalizerExceptionFactory::createForUndenormalizableConstructor();
        $this->assertEquals(
            'Could not denormalize the given constructor.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForUndenormalizableFactory()
    {
        $exception = DenormalizerExceptionFactory::createForUndenormalizableFactory();
        $this->assertEquals(
            'Could not denormalize the given factory.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForUnparsableValue()
    {
        $code = 500;
        $previous = new \Error();

        $exception = DenormalizerExceptionFactory::createForUnparsableValue('foo', $code, $previous);
        $this->assertEquals(
            'Could not parse value "foo".',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testTestCreateDenormalizerNotFoundForFixture()
    {
        $exception = DenormalizerExceptionFactory::createDenormalizerNotFoundForFixture('foo');

        $this->assertEquals(
            'No suitable fixture denormalizer found to handle the fixture with the reference "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateDenormalizerNotFoundUnexpectedCall()
    {
        $exception = DenormalizerExceptionFactory::createDenormalizerNotFoundUnexpectedCall('fake');

        $this->assertEquals(
            'Expected method "fake" to be called only if it has a denormalizer.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }

    public function testTestCreateForInvalidScopeForUniqueValue()
    {
        $exception = DenormalizerExceptionFactory::createForInvalidScopeForUniqueValue();

        $this->assertEquals(
            'Cannot bind a unique value scope to a temporary fixture.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
