<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\FlagParser;

class FlagParserNotFoundException extends \RuntimeException
{
    public static function create(string $element): self
    {
        return new static(
            sprintf(
                'No suitable flag parser found to handle the element "%s".',
                $element
            )
        );
    }
}
