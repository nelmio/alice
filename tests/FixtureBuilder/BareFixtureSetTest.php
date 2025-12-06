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
use Nelmio\Alice\FixtureBagTest;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\ParameterBagTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(BareFixtureSet::class)]
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

    #[Depends(ParameterBagTest::testIsImmutable)]
    #[Depends(FixtureBagTest::testIsImmutable)]
    public function testIsImmutable(): void
    {
        self::assertTrue(true, 'Nothing to do.');
    }
}
