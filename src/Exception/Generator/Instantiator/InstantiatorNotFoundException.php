<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\Generator\Instantiator;

use Nelmio\Alice\FixtureInterface;

class InstantiatorNotFoundException extends \LogicException
{
    public static function create(FixtureInterface $fixture): self
    {
        return new static(
            sprintf(
                'No suitable instantiator found for the fixture "%s".',
                $fixture->getId()
            )
        );
    }
}
