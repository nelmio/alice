<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Parser\Methods;

interface MethodInterface
{
    /**
     * Tests whether this class can parse the given file.
     *
     * @param string $file File path
     *
     * @return boolean
     */
    public function canParse($file);

    /**
     * Parses the file and converts it into a php array.
     *
     * @param string $file File path
     *
     * @return array
     */
    public function parse($file);
}
