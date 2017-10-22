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

namespace Nelmio\Alice\Generator\Resolver\Fixture;

use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureResolver
 *
 * More tests in:
 *
 * @see \Nelmio\Alice\Generator\Resolver\Fixture\TemplateFixtureBagResolverTest
 */
class TemplateFixtureResolverTest extends TestCase
{
    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(TemplateFixtureResolver::class))->isCloneable());
    }
}
