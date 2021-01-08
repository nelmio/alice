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
 * Value object representing '$username'.
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
    
    public function getValue(): string
    {
        return $this->variable;
    }
    
    public function __toString(): string
    {
        return sprintf('$%s', $this->variable);
    }
}
