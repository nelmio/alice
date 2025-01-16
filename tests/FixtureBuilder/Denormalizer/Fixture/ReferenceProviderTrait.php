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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture;

trait ReferenceProviderTrait
{
    public static function provideSimpleFixtures(): array
    {
        return Reference::getSimpleFixtures();
    }

    public static function provideListFixtures(): array
    {
        return Reference::getListFixtures();
    }

    public static function provideMalformedListFixtures(): array
    {
        return Reference::getMalformedListFixtures();
    }

    public static function provideSegmentFixtures(): array
    {
        return Reference::getSegmentFixtures();
    }

    public static function provideMalformedSegmentFixtures(): array
    {
        return Reference::getMalformedSegmentFixtures();
    }
}
