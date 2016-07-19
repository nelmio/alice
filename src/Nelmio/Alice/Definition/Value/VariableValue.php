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
 * VO representing '$username'.
 */
final class VariableValue implements ValueInterface
{
    /**
     * @var string
     */
    private $variable;

    /**
     * @param string $variable e.g. 'username'
     */
    public function __construct(string $variable)
    {
        $this->variable = $variable;
    }

    /**
     * @inheritdoc
     */
    public function getValue(): string
    {
        return $this->variable;
    }
}
