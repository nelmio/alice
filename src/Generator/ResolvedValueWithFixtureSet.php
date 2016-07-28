<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

/**
 * Simple VO containing a value and a resolved fixture set.
 */
final class ResolvedValueWithFixtureSet
{
    /**
     * @var mixed
     */
    private $value;
    
    /**
     * @var ResolvedFixtureSet
     */
    private $set;

    /**
     * @param mixed              $resolvedValue
     * @param ResolvedFixtureSet $set
     */
    public function __construct($resolvedValue, ResolvedFixtureSet $set)
    {
        $this->value = $resolvedValue;
        $this->set = $set;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        $value = $this->value;
        
        return is_object($value) ? clone $value : $value;
    }

    public function getSet(): ResolvedFixtureSet
    {
        return clone $this->set;
    }
}
