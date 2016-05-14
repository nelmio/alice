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
     * @return mixed
     */
    public function getValue();

    /**
     * Tests whether this property's value matches the regex, and appends new matches to the matches array.
     *
     * @param string $regex
     *
     * @return boolean
     */
    public function valueMatches($regex);

    /**
     * Gets the match of a named group.
     *
     * @param int|string $name
     *
     * @return string|null
     */
    public function getMatch($name);

    /**
     * Return all matches.
     *
     * @return string[]
     */
    public function getMatches();
}
