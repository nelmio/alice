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

namespace Nelmio\Alice\Entity;

use BadMethodCallException;

class OnceTimerDummy
{
    private $relatedDummy;
    private $hydrate = false;
    private $call = false;

    public function setHydrate($hydrate): void
    {
        if ($this->hydrate) {
            throw new BadMethodCallException();
        }

        $this->hydrate = $hydrate;
    }

    public function call($call): void
    {
        if ($this->call) {
            throw new BadMethodCallException();
        }

        $this->call = $call;
    }

    public function setRelatedDummy($dummy): void
    {
        if (null !== $this->relatedDummy) {
            throw new BadMethodCallException();
        }

        $this->relatedDummy = $dummy;
    }
}
