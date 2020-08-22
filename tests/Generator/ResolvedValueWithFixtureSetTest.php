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

namespace Nelmio\Alice\Generator;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Nelmio\Alice\Generator\ResolvedValueWithFixtureSet
 */
class ResolvedValueWithFixtureSetTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $value = new stdClass();
        $set = ResolvedFixtureSetFactory::create();

        $resolvedValueWithSet = new ResolvedValueWithFixtureSet($value, $set);

        static::assertEquals($value, $resolvedValueWithSet->getValue());
        static::assertEquals($set, $resolvedValueWithSet->getSet());
    }
}
