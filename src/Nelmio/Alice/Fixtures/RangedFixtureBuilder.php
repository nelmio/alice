<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures;

final class RangedFixtureBuilder
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $currentValue;

    /**
     * @var string
     */
    private $flags;

    /**
     * @var array
     */
    private $specs;

    /**
     * @param string $className
     * @param string $name
     * @param array  $specs
     * @param string $flags
     * @param string $currentValue
     */
    public function __construct(string $className, string $name, array $specs, string $flags, string $currentValue)
    {
        $this->className = $className;
        $this->name = $name;
        $this->specs = $specs;
        $this->flags = $flags;
        $this->currentValue = $currentValue;
    }
}
