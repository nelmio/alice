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

namespace Nelmio\Alice\Throwable\Exception\PropertyAccess;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\PropertyAccess\NoSuchPropertyExceptionFactory
 */
class NoSuchPropertyExceptionFactoryTest extends TestCase
{
    public function testCreateForUnreadablePropertyFromStdClass()
    {
        $exception = NoSuchPropertyExceptionFactory::createForUnreadablePropertyFromStdClass('foo');

        $this->assertEquals(
            'Cannot read property "foo" from stdClass.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
