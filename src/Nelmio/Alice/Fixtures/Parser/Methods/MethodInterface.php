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
     * tests whether this class can parse the given file
     *
     * @param  string  $file
     * @return boolean
     */
    public function canParse($file);

    /**
     * parses the file and converts it into a php array
     *
     * @param  string $file
     * @return array
     */
    public function parse($file);
}
