<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\FlagParser;

use Nelmio\Alice\Builder\FlagParserInterface;

final class SanitizeParser implements FlagParserInterface
{
    /**
     * @inheritdoc
     */
    public function parse(string $key): array
    {
        $matches = [];
        if (1 !== preg_match('/^(?:.+?)\s*\((.+)\)$/', $key, $matches)) {
            return [];
        }

        $flags = [];
        $rawFlags = explode(',', $matches[1]);
        foreach ($rawFlags as $rawFlag) {
            $flag = trim($rawFlag);
            if ('' !== $flag) {
                $flags[$flag] = true;
            }
        }

        return array_keys($flags);
    }
}
