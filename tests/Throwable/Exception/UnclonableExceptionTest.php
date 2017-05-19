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

/**
 * @covers \Nelmio\Alice\Throwable\Exception\UnclonableException
 */
class UnclonableExceptionTest extends TestCase
{
    public function testIsADomainException()
    {
        $this->assertTrue(is_a(UnclonableException::class, \DomainException::class, true));
    }

    public function testTestCreateExceptionForAService()
    {
        $exception = UnclonableException::createForService();

        $this->assertEquals(
            'This class is a service and as such should not be cloned. A service is not necessarily stateless and as '
            .'such cloning it may result in weird side effects. You should either create a new instance or make use '
            .'of a static or non static factory instead.',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
