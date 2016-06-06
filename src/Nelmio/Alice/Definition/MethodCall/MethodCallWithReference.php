<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\MethodCall;

use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ServiceReferenceInterface;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\NotClonableTrait;

final class MethodCallWithReference implements MethodCallInterface
{
    use NotClonableTrait;

    /**
     * @var ServiceReferenceInterface
     */
    private $caller;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param ServiceReferenceInterface $caller
     * @param string                    $method
     * @param ValueInterface[]|mixed[]  $arguments
     */
    public function __construct(ServiceReferenceInterface $caller, string $method, array $arguments)
    {
        $this->caller = $caller;
        $this->method = $method;
        $this->arguments = $arguments;
    }

    /**
     * @inheritdoc
     */
    public function getCaller()
    {
        return clone $this->caller;
    }

    /**
     * @inheritdoc
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function getArguments(): array 
    {
        return $this->arguments;
    }
}
