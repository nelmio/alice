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

namespace Nelmio\Alice\Generator\Instantiator;

use AllowDynamicProperties;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\NotCallableTrait;

#[AllowDynamicProperties]
class FakeChainableInstantiator implements ChainableInstantiatorInterface
{
    use NotCallableTrait;

    public function canInstantiate(FixtureInterface $fixture): never
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function instantiate(FixtureInterface $fixture, ResolvedFixtureSet $fixtureSet, GenerationContext $context): never
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
