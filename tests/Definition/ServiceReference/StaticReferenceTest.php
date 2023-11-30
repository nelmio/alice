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

namespace Nelmio\Alice\Definition\ServiceReference;

use Nelmio\Alice\Definition\ServiceReferenceInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Definition\ServiceReference\StaticReference
 * @internal
 */
class StaticReferenceTest extends TestCase
{
    public function testIsAReference(): void
    {
        self::assertTrue(is_a(StaticReference::class, ServiceReferenceInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $reference = 'Nelmio\User\UserFactory';
        $definition = new StaticReference($reference);

        self::assertEquals($reference, $definition->getId());
    }
}
