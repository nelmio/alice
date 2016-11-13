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

namespace Nelmio\Alice\Throwable\Exception;

use Nelmio\Alice\FixtureInterface;

class NoValueForCurrentException extends \RuntimeException
{
    /**
     * @return static
     */
    public static function create(FixtureInterface $fixture, int $code = 0, \Throwable $previous = null)
    {
        return new static(
            sprintf(
                'No value for \'<current()>\' found for the fixture "%s".',
                $fixture->getId()
            ),
            $code,
            $previous
        );
    }
}
