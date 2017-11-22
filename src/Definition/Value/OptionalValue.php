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

/**
 * Value object representing "80%? 'value': 'empty'"
 */
final class OptionalValue implements ValueInterface
{
    /**
     * @var int|ValueInterface
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
     * @param int|string|ValueInterface         $quantifier
     * @param string|ValueInterface      $firstMember
     * @param string|ValueInterface|null $secondMember
     */
    public function __construct($quantifier, $firstMember, $secondMember = null)
    {
        if ($quantifier instanceof ValueInterface) {
            $quantifier = clone $quantifier;
        } elseif (is_scalar($quantifier)) {
            $quantifier = (int) $quantifier;
        } else {
            throw TypeErrorFactory::createForOptionalValueQuantifier($quantifier);
        }

        if (false === is_string($firstMember) && false === $firstMember instanceof ValueInterface) {
            throw TypeErrorFactory::createForOptionalValueFirstMember($firstMember);
        }

        if (null !== $secondMember && false === is_string($secondMember) && false === $secondMember instanceof ValueInterface) {
            throw TypeErrorFactory::createForOptionalValueSecondMember($secondMember);
        }

        $this->quantifier = $quantifier;
        $this->firstMember = $firstMember;
        $this->secondMember = $secondMember;
    }

    /**
     * @return int|ValueInterface
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

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return sprintf(
            '%s%%? %s : %s',
            $this->quantifier,
            $this->firstMember,
            null === $this->secondMember? 'null' : $this->secondMember
        );
    }
}
