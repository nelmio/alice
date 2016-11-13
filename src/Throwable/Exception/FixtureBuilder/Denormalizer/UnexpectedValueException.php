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

namespace Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer;

use Nelmio\Alice\Throwable\DenormalizationThrowable;

class UnexpectedValueException extends \UnexpectedValueException implements DenormalizationThrowable
{
    public static function createForUnparsableValue(string $value, int $code = 0, \Throwable $previous): self
    {
        return new static(
            sprintf(
                'Could not parse value "%s".',
                $value
            ),
            $code,
            $previous
        );
    }

    public static function createForUnDenormalizableConstructor(): self
    {
        return new static('Could not denormalize the given constructor.');
    }
}
