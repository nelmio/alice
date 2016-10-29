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

namespace Nelmio\Alice\Definition\ServiceReference;

use Nelmio\Alice\Definition\ServiceReferenceInterface;
use Nelmio\Alice\NotCallableTrait;

class FakeReference implements ServiceReferenceInterface
{
    use NotCallableTrait;

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
