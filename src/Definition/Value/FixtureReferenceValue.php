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
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

/**
 * Value object representing "@user0".
 */
final class FixtureReferenceValue implements ValueInterface
{
    /**
     * @var string|ValueInterface
     */
    private $reference;

    /**
     * @param string|ValueInterface $reference e.g. "user0"
     */
    public function __construct($reference)
    {
        if (false === is_string($reference) && false === $reference instanceof ValueInterface) {
            if (null === $reference) {
                $referenceString = 'null';
            } elseif (is_array($reference)) {
                $referenceString = 'array';
            } else {
                $referenceString = is_scalar($reference) ? gettype($reference) : get_class($reference);
            }

            throw InvalidArgumentExceptionFactory::createForInvalidReferenceType($referenceString);
        }

        $this->reference = $reference;
    }

    /**
     * {@inheritdoc}
     *
     * @return string|ValueInterface
     */
    public function getValue()
    {
        return $this->reference;
    }

    /**
     * {@inheritdoc}
     *
     * @return string|ValueInterface
     */
    public function __toString(): string
    {
        return sprintf('@%s', $this->reference);
    }
}
