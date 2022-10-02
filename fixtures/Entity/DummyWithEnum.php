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

use Nelmio\Alice\Entity\Enum\DummyEnum;

class DummyWithEnum
{
    private DummyEnum $dummyEnum;

    public function setDummyEnum(DummyEnum $dummyEnum): void
    {
        $this->dummyEnum = $dummyEnum;
    }

    public function getDummyEnum(): DummyEnum
    {
        return $this->dummyEnum;
    }
}
