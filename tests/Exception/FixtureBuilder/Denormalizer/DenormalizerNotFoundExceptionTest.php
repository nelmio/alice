<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\FixtureBuilder\Denormalizer;

use Nelmio\Alice\Throwable\DenormalizationThrowable;

/**
 * @covers \Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\DenormalizerNotFoundException
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
    }

    public function testTestCreateNewExceptionWithFactoryForUnexpectedCall()
    {
        $exception = DenormalizerNotFoundException::createUnexpectedCall('fake');

        $this->assertEquals(
            'Expected method "fake" to be called only if it has a denormalizer.',
            $exception->getMessage()
        );
    }
}
