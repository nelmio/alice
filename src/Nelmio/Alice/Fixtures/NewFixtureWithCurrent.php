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

final class NewFixtureWithCurrent extends NewFixture
{
    /**
     * @var string
     */
    private $valueForCurrent;

    public function __construct(string $className, string $name, array $specs, string $valueForCurrent)
    {
        parent::__construct($className, $name, $specs);

        $this->valueForCurrent = $valueForCurrent;
    }

    public function getValueForCurrent(): string
    {
        return $this->valueForCurrent;
    }
}
