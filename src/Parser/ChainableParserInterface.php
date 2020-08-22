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

namespace Nelmio\Alice\Parser;

use Nelmio\Alice\ParserInterface;

interface ChainableParserInterface extends ParserInterface
{
    /**
     * @param string $file File path
     */
    public function canParse(string $file): bool;
}
