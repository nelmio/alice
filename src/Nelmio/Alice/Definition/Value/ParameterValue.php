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
 * VO representing '<{param}>'.
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
        $this->parameterKey = $parameterKey;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return is_object($this->parameterKey) ? clone $this->parameterKey: $this->parameterKey;
    }

    public function __clone()
    {
        $this->parameterKey = $this->getValue();
    }
}
