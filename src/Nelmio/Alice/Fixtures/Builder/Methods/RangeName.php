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
        if (1 === preg_match('/\{(?<from>[0-9]+)(?<deprecated>\.{3,})(?<to>[0-9]+)\}/', $name, $this->matches)) {
            @trigger_error(
                'Ranged name should follow the pattern "name{X..Y}". Using "name{X...Y} or with more dots instead is '
                .'deprecated since 2.2.0 and will be removed in 3.0. Please mind the change of behavior: "user{0..10}"'
                .'is creating 11 users whereas "user{0...10}" is creating 10',
                E_USER_DEPRECATED
            );

            return true;
        }

        return 1 === preg_match('/\{(?<from>[0-9]+)(?:\.{2})(?<to>[0-9]+)\}/', $name, $this->matches);
    }

    /**
     * {@inheritDoc}
     */
    public function build($class, $name, array $spec)
    {
        $from = $this->matches['from'];
        $to = $this->matches['to'];

        if ($from > $to) {
            list($to, $from) = [$from, $to];
        }
        if (isset($this->matches['deprecated'])) {
            $to -= 1;
        }

        $fixtures = [];
        for ($currentIndex = $from; $currentIndex <= $to; $currentIndex++) {
            $currentName = str_replace($this->matches[0], $currentIndex, $name);
            $fixture = new Fixture($class, $currentName, $spec, (string) $currentIndex);
            $fixtures[] = $fixture;
        }

        return $fixtures;
    }
}
