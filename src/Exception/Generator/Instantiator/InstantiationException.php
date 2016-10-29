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

namespace Nelmio\Alice\Exception\Generator\Instantiator;

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Throwable\InstantiationThrowable;

class InstantiationException extends \RuntimeException implements InstantiationThrowable
{
    /**
     * @return static
     */
    public static function create(FixtureInterface $fixture, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'Could not instantiate fixture "%s".',
                $fixture->getId()
            ),
            $code,
            $previous
        );
    }
}
