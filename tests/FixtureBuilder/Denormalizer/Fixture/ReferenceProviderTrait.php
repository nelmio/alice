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
    public function provideSimpleFixtures()
    {
        return Reference::getSimpleFixtures();
    }

    public function provideListFixtures()
    {
        return Reference::getListFixtures();
    }

    public function provideMalformedListFixtures()
    {
        return Reference::getMalformedListFixtures();
    }

    public function provideSegmentFixtures()
    {
        return Reference::getSegmentFixtures();
    }

    public function provideMalformedSegmentFixtures()
    {
        return Reference::getMalformedSegmentFixtures();
    }
}
