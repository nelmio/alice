<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\FixtureBuilder\Parser;

use Nelmio\Alice\Throwable\ParseThrowable;

class ParserNotFoundException extends \RuntimeException implements ParseThrowable
{
    public static function create(string $file): self
    {
        return new static(
            sprintf(
                'No suitable parser found for the file "%s".',
                $file
            )
        );
    }
}
