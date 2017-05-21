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

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\Throwable\Error\TypeErrorFactory;
use function Nelmio\Alice\deep_clone;

/**
 * Value object representing an array like "10x @user0". '10' is called "quantifier" and "@user0" is called "element".
 */
final class DynamicArrayValue implements ValueInterface
{
    /**
     * @var int|ValueInterface
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
        } elseif (false === is_int($quantifier)) {
            throw TypeErrorFactory::createForDynamicArrayQuantifier($quantifier);
        }

        if (false === is_string($element) && false === is_array($element) && false === $element instanceof ValueInterface) {
            throw TypeErrorFactory::createForDynamicArrayElement($element);
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

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return sprintf('%sx %s', $this->quantifier, $this->element);
    }
}
