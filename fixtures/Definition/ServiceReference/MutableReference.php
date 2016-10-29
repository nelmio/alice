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

class MutableReference implements ServiceReferenceInterface
{
    private $id = 'default';

    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->id;
    }
}
