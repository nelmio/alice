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
    public static function provideSimpleFixtures(): iterable
    {
        return Reference::getSimpleFixtures();
    }

    public static function provideListFixtures(): iterable
    {
        return Reference::getListFixtures();
    }

    public static function provideMalformedListFixtures(): iterable
    {
        return Reference::getMalformedListFixtures();
    }

    public static function provideSegmentFixtures(): iterable
    {
        return Reference::getSegmentFixtures();
    }

    public static function provideMalformedSegmentFixtures(): iterable
    {
        return Reference::getMalformedSegmentFixtures();
    }
}
