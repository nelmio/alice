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
 * VO representing "80%? 'value': 'empty'"
 */
final class OptionalValue implements ValueInterface
{
    /**
     * @var string|ValueInterface
     */
    private $quantifier;

    /**
     * @var string|ValueInterface
     */
    private $firstMember;

    /**
     * @var string|ValueInterface|null
     */
    private $secondMember;

    /**
     * @param string|ValueInterface      $quantifier
     * @param string|ValueInterface      $firstMember
     * @param string|ValueInterface|null $secondMember
     */
    public function __construct($quantifier, $firstMember, $secondMember = null)
    {
        $this->quantifier = $quantifier;
        $this->firstMember = $firstMember;
        $this->secondMember = $secondMember;
    }

    /**
     * @return string|ValueInterface
     */
    public function getQuantifier()
    {
        return is_object($this->quantifier) ? clone $this->quantifier : $this->quantifier;
    }

    /**
     * @return string|ValueInterface
     */
    public function getFirstMember()
    {
        return is_object($this->firstMember) ? clone $this->firstMember : $this->firstMember;
    }

    /**
     * @return ValueInterface|null|string
     */
    public function getSecondMember()
    {
        return is_object($this->secondMember) ? clone $this->secondMember : $this->secondMember;
    }

    /**
     * {@inheritdoc}
     *
     * @return array The first element is the quantifier and the second the elements.
     */
    public function getValue(): array
    {
        return [
            $this->getQuantifier(),
            $this->getFirstMember(),
            $this->getSecondMember(),
        ];
    }

    public function __clone()
    {
        list($this->quantifier, $this->firstMember, $this->secondMember) = $this->getValue();
    }
}
