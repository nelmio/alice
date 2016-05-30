<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixture;

final class Argument
{
    /**
     * @var mixed
     */
    private $value;

    /**
     * @var bool
     */
    private $requiresUnique;
    
    /**
     * @var string
     */
    private $token;

    public function __construct(string $token, $value, bool $requiresUnique = false)
    {
        $this->token = $token;
        $this->value = $value;
        $this->requiresUnique = $requiresUnique;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return (is_object($this->value))? clone $this->value : $this->value;
    }

    /**
     * Returns true if this property requires unique values.
     *
     * @return boolean
     */
    public function requiresUnique(): bool
    {
        return $this->requiresUnique;
    }
    
    public function getUniqueToken(): string
    {
        return $this->token;
    }
}
