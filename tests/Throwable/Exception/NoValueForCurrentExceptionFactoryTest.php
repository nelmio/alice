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

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\NoValueForCurrentExceptionFactory
 */
class NoValueForCurrentExceptionFactoryTest extends TestCase
{
    public function testCreateException()
    {
        $exception = NoValueForCurrentExceptionFactory::create(new DummyFixture('dummy'));

        $this->assertEquals(
            'No value for \'<current()>\' found for the fixture "dummy".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
