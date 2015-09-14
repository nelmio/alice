<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor;

interface ProcessableInterface
{
    /**
     * @return string
     **/
    public function getValue();

    /**
     * tests whether this property's value matches the regex, and appends new matches to the matches array
     *
     * @param  string  $regexString
     * @return boolean
     */
    public function valueMatches($regexString);

    /**
     * allows us to access the list of matches from outside the property class
     *
     * @param  string $name
     * @return string
     */
    public function getMatch($name);

    /**
     * return all matches
     *
     * @return string[]
     */
    public function getMatches();
}
