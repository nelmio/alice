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

namespace Nelmio\Alice\Entity\Instantiator;

use AllowDynamicProperties;

#[AllowDynamicProperties]
class DummyWithOptionalParameterInConstructor
{
    public function __construct(int $optionalParam = 10)
    {
        $this->optionalParam = $optionalParam;
    }
}
