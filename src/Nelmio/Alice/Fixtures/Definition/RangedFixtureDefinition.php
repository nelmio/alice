<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Definition;

final class UnresolvedRangedFixtureDefinition extends UnresolvedFixtureDefinition
{
    /**
     * @var string
     */
    private $valueForCurrent;

    /**
     * {@inheritdoc}
     *
     * @param string $valueForCurrent Value for the <current()> function
     */
    public function __construct(string $className, string $name, array $specs, string $flags, string $valueForCurrent)
    {
        parent::__construct($className, $name, $specs, $flags);

        $this->valueForCurrent = $valueForCurrent;
    }

    public function getValueForCurrent(): string
    {
        return $this->valueForCurrent;
    }
}
