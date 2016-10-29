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

namespace Nelmio\Alice\Generator\Instantiator\Chainable;

use Nelmio\Alice\FixtureInterface;

class DummyChainableInstantiator extends AbstractChainableInstantiator
{
    /**
     * @inheritdoc
     */
    protected function createInstance(FixtureInterface $fixture)
    {
        return new \stdClass();
    }

    /**
     * @inheritdoc
     */
    public function canInstantiate(FixtureInterface $fixture): bool
    {
        return true;
    }
}
