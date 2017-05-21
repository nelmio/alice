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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\ObjectGenerator\ObjectGeneratorNotFoundExceptionFactory
 */
class ObjectGeneratorNotFoundExceptionFactoryTest extends TestCase
{
    public function testCreateNewExceptionWithFactory()
    {
        $exception = ObjectGeneratorNotFoundExceptionFactory::createUnexpectedCall('dummyMethod');

        $this->assertEquals(
            'Expected method "dummyMethod" to be called only if it has a generator.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
