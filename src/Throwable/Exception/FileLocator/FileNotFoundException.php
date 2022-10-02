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

namespace Nelmio\Alice\Throwable\Exception\FileLocator;

use UnexpectedValueException;

class FileNotFoundException extends UnexpectedValueException
{
    public static function createForEmptyFile(): static
    {
        return new static('An empty file name is not valid to be located.');
    }

    public static function createForNonExistentFile(string $file): static
    {
        return new static(
            sprintf(
                'The file "%s" does not exist.',
                $file
            )
        );
    }
}
