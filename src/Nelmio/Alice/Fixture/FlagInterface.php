<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

/**
 * A flag is used to pass contextual data to the fixture, e.g. to specify that a fixture is a template or extend a
 * peculiar template.
 */
interface FlagInterface
{
    /**
     * @return string Flag string representation. Is used as an identifier to easily access to a specific flag.
     */
    public function __toString(): string;
}
