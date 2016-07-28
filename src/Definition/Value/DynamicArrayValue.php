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

/**
 * VO representing a array like "10x @user0". '10' is called "quantifier" and "@user0" is called "element".
 */
final class DynamicArrayValue implements ValueInterface
{
    /**
     * @var string|ValueInterface
     */
    private $quantifier;

    /**
     * @var string|ValueInterface
     */
    private $element;

    /**
     * @param string|ValueInterface $quantifier
     * @param string|ValueInterface $element
     */
    public function __construct($quantifier, $element)
    {
        $this->quantifier = $quantifier;
        $this->element = $element;
    }

    /**
     * @return int|ValueInterface
     */
    public function getQuantifier()
    {
        return is_object($this->quantifier) ? clone $this->quantifier : (int) $this->quantifier;
    }

    /**
     * @return string|ValueInterface
     */
    public function getElement()
    {
        return is_object($this->element) ? clone $this->element : $this->element;
    }

    /**
     * {@inheritdoc}
     *
     * @return array The first element is the quantifier and the second the element.
     */
    public function getValue(): array
    {
        return [
            $this->getQuantifier(),
            $this->getElement(),
        ];
    }

    public function __clone()
    {
        list($this->quantifier, $this->element) = $this->getValue();
    }
}
