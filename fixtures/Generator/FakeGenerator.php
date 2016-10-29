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

use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\GeneratorInterface;
use Nelmio\Alice\NotCallableTrait;
use Nelmio\Alice\ObjectSet;

class FakeGenerator implements GeneratorInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function generate(FixtureSet $fixtureSet): ObjectSet
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
