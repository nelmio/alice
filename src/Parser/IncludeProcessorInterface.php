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
use Nelmio\Alice\Throwable\ParseThrowable;

interface IncludeProcessorInterface
{
    /**
     * Process the data include/import statements. For example check the files in the include statement and parses
     * those files and merges the result with the existing data.
     *
     * @param ParserInterface $parser Parsed used to parse the files to include.
     * @param string          $file   File from which the data comes from.
     * @param array           $data   Parse result of the loaded file.
     *
     * @throws ParseThrowable
     *
     * @return array
     */
    public function process(ParserInterface $parser, string $file, array $data): array;
}
