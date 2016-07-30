<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\FixtureBuilder\Denormalizer;

class DenormalizerNotFoundException extends \LogicException
{
    public static function createForFixture(string $fixtureId)
    {
        return new static(
            sprintf(
                'No suitable fixture denormalizer found to handle the fixture with the reference "%s".',
                $fixtureId
            )
        );
    }

    public static function createUnexpectedCall(string $method)
    {
        return new static(
            sprintf(
                'Expected method "%s" to be called only if it has a denormalizer.',
                $method
            )
        );
    }
}
