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

namespace Nelmio\Alice\FixtureBuilder;

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\CoversClass(BareFixtureSet::class)]
final class BareFixtureSetTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $set = new BareFixtureSet(
            $parameters = (new ParameterBag())->with(new Parameter('foo', 'bar')),
            $fixtures = (new FixtureBag())->with(new DummyFixture('foo')),
        );

        self::assertEquals($parameters, $set->getParameters());
        self::assertEquals($fixtures, $set->getFixtures());
    }

    #[\PHPUnit\Framework\Attributes\Depends(\Nelmio\Alice\ParameterBagTest::testIsImmutable)]
    #[\PHPUnit\Framework\Attributes\Depends(\Nelmio\Alice\FixtureBagTest::testIsImmutable)]
    public function testIsImmutable(): void
    {
        self::assertTrue(true, 'Nothing to do.');
    }
}
