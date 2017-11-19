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

namespace Nelmio\Alice\Generator;

/**
 * Simple value object containing a value and a resolved fixture set.
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
        return $this->value;
    }

    public function getSet(): ResolvedFixtureSet
    {
        return $this->set;
    }
}
