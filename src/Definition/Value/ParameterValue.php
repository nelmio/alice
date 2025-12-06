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
use Stringable;
use function Nelmio\Alice\deep_clone;

/**
 * Value object representing '<{param}>'.
 */
final class ParameterValue implements Stringable, ValueInterface
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
            throw TypeErrorFactory::createForInvalidParameterKey($parameterKey);
        }

        $this->parameterKey = $parameterKey;
    }

    public function getValue()
    {
        return deep_clone($this->parameterKey);
    }

    public function __toString(): string
    {
        return sprintf('<{%s}>', $this->parameterKey);
    }
}
