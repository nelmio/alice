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

class MutableFlag implements FlagInterface
{
    /**
     * @var string
     */
    private $stringValue;

    /**
     * @var
     */
    private $object;

    public function __construct(string $stringValue, $object)
    {
        $this->stringValue = $stringValue;
        $this->object = $object;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->stringValue;
    }

    public function setStringValue(string $value): void
    {
        $this->stringValue = $value;
    }

    public function getObject()
    {
        return $this->object;
    }
}
