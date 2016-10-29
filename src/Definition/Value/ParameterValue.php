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

/**
 * Value object representing '<{param}>'.
 */
final class ParameterValue implements ValueInterface
{
    /**
     * @var string|ValueInterface
     */
    private $parameterKey;

    /**
     * @param string|ValueInterface $parameterKey e.g. 'dummy_param'
     */
    public function __construct($parameterKey)
    {
        if (false === is_string($parameterKey) && false === $parameterKey instanceof ValueInterface) {
            throw new \TypeError(
                sprintf(
                    'Expected parameter key to be either a string or an instance of "%s". Got "%s" instead.',
                    ValueInterface::class,
                    is_object($parameterKey) ? get_class($parameterKey) : gettype($parameterKey)
                )
            );
        }
        $this->parameterKey = $parameterKey;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return deep_clone($this->parameterKey);
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return sprintf('<{%s}>', $this->parameterKey);
    }
}
