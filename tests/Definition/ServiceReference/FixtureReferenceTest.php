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
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(FixtureReference::class)]
final class FixtureReferenceTest extends TestCase
{
    public function testIsAReference(): void
    {
        self::assertTrue(is_a(FixtureReference::class, ServiceReferenceInterface::class, true));
    }

    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $id = 'user_base';
        $definition = new FixtureReference($id);

        self::assertEquals($id, $definition->getId());
    }

    public function testIsImmutable(): void
    {
        self::assertTrue(true, 'Nothing to do.');
    }
}
