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

namespace Nelmio\Alice\Definition\Flag;

use Nelmio\Alice\Definition\FlagInterface;

final class ConfiguratorFlag implements FlagInterface
{
    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return 'configurator';
    }
}
