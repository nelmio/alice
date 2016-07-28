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
 * Value object representing an array like "10x @user0". '10' is called "quantifier" and "@user0" is called "element".
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
     * @param int|ValueInterface    $quantifier
     * @param string|ValueInterface $element
     */
    public function __construct($quantifier, $element)
    {
        if ($quantifier instanceof ValueInterface) {
            $quantifier = clone $quantifier;
        } elseif (is_scalar($quantifier)) {
            $quantifier = (int) $quantifier;
        } else {
            throw new \TypeError(
                sprintf(
                    'Expected quantifier to be either a scalar value or a "%s" object. Got "%s" instead.',
                    ValueInterface::class,
                    is_object($quantifier) ? get_class($quantifier) : gettype($quantifier)
                )
            );
        }

        if (false === is_string($element) && false === $element instanceof ValueInterface) {
            throw new \TypeError(
                sprintf(
                    'Expected element to be either string or a "%s" object. Got "%s" instead.',
                    ValueInterface::class,
                    is_object($element) ? get_class($element) : gettype($element)
                )
            );
        }

        $this->quantifier = $quantifier;
        $this->element = deep_clone($element);
    }

    /**
     * @return int|ValueInterface
     */
    public function getQuantifier()
    {
        return deep_clone($this->quantifier);
    }

    /**
     * @return string|ValueInterface
     */
    public function getElement()
    {
        return deep_clone($this->element);
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
}
