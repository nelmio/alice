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

class RangeName implements MethodInterface
{
    private $matches = [];

    /**
     * {@inheritDoc}
     */
    public function canBuild($name)
    {
        if (1 === preg_match('/\{([0-9]+)\.{3,}([0-9]+)\}/', $name, $this->matches)) {
            @trigger_error(
                'Ranged name should follow the pattern "name{X..Y}". Using "name{X...Y} instead is now deprecated and '
                .'will be removed in 3.0',
                E_USER_DEPRECATED
            );

            return true;
        }

        return 1 === preg_match('/\{([0-9]+)\.{2,}([0-9]+)\}/', $name, $this->matches);
    }

    /**
     * {@inheritDoc}
     */
    public function build($class, $name, array $spec)
    {
        $fixtures = [];

        $from = $this->matches[1];
        $to = $this->matches[2] - 1;
        if ($from > $to) {
            list($to, $from) = [$from, $to];
        }
        for ($currentIndex = $from; $currentIndex <= $to; $currentIndex++) {
            $currentName = str_replace($this->matches[0], $currentIndex, $name);
            $fixture = new Fixture($class, $currentName, $spec, (string) $currentIndex);
            $fixtures[] = $fixture;
        }

        return $fixtures;
    }
}
