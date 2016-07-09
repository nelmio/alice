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

final class FunctionCallValue implements ValueInterface
{
    /**
     * @var string|ValueInterface
     */
    private $parameterKey;

    /**
     * @param string|ValueInterface $parameterKey Can be a value for dynamic parameters.
     */
    public function __construct(string $method, array $arguments = null)
    {
        $this->parameterKey = $parameterKey;
    }
    
    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->parameterKey;
    }
}
