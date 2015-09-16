<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder\Methods;

use Nelmio\Alice\Fixtures\Fixture;

class ListName implements MethodInterface
{
    /**
     * @var string[]
     */
    private $matches = [];

    /**
     * {@inheritDoc}
     */
    public function canBuild($name)
    {
        return 1 === preg_match('#\{([^,]+(\s*,\s*[^,]+)*)\}#', $name, $this->matches);
    }

    /**
     * {@inheritDoc}
     */
    public function build($class, $name, array $spec)
    {
        $fixtures = [];

        $enumItems = array_map('trim', explode(',', $this->matches[1]));
        foreach ($enumItems as $itemName) {
            $currentName = str_replace($this->matches[0], $itemName, $name);
            $fixture = new Fixture($class, $currentName, $spec, $itemName);
            $fixtures[] = $fixture;
        }

        return $fixtures;
    }
}
