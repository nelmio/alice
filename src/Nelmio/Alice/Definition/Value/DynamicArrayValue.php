<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;

final class DynamicArrayValue implements ValueInterface
{
    /**
     * @var float|int|ValueInterface
     */
    private $quantifier;

    /**
     * @var ValueInterface|string
     */
    private $elements;

    /**
     * @param ValueInterface|int|float $quantifier
     * @param ValueInterface|string    $elements
     */
    public function __construct($quantifier, $elements)
    {
        $this->quantifier = $quantifier;
        $this->elements = $elements;
    }

    /**
     * @return float|int|ValueInterface
     */
    public function getQuantifier()
    {
        return $this->quantifier;
    }

    /**
     * @return string|ValueInterface
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * {@inheritdoc}
     * 
     * @return array The first element is the quantifier and the second the elements.
     */
    public function getValue(): array
    {
        return [$this->quantifier, $this->elements];
    }
}
