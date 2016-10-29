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

use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotCallableTrait;
use Nelmio\Alice\ObjectBag;

class FakeObjectGenerator implements ObjectGeneratorInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function generate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet, GenerationContext $context): ObjectBag
    {
        $this->__call(__FUNCTION__, func_get_args());
    }
}
