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

class ElementWithToStringFlag implements FlagInterface
{
    /**
     * @var string
     */
    private $element;

    /**
     * @var string
     */
    private $toString;

    public function __construct(string $element, string $toString)
    {
        $this->element = $element;
        $this->toString = $toString;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->toString;
    }
}
