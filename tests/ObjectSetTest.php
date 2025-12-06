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

namespace Nelmio\Alice;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
#[CoversClass(ObjectSet::class)]
final class ObjectSetTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $parameters = new ParameterBag([
            'foo' => 'bar',
        ]);
        $objects = new ObjectBag([
            'dummy' => $std = new stdClass(),
        ]);

        $set = new ObjectSet($parameters, $objects);

        self::assertSame(
            [
                'foo' => 'bar',
            ],
            $set->getParameters(),
        );
        self::assertEquals(
            [
                'dummy' => $std,
            ],
            $set->getObjects(),
        );
        self::assertCount(1, $set->getObjects());
    }
}
