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
use Nelmio\Alice\Throwable\InstantiationThrowable;

class InstantiationException extends \RuntimeException implements InstantiationThrowable
{
    public static function create(FixtureInterface $fixture, \Throwable $previous)
    {
        return new static(
            sprintf(
                'Could no instantiate fixture "%s".',
                $fixture->getReference()
            ),
            0,
            $previous
        );
    }
}
