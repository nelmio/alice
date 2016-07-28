<?php

/**
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\MethodCall;

use Nelmio\Alice\Definition\MethodCallInterface;

final class DummyMethodCall implements MethodCallInterface
{
    /**
     * @var string
     */
    private $toString;

    /**
     * @var string
     */
    private $token;

    public function __construct(string $toString)
    {
        $this->token = uniqid();
        $this->toString = $toString;
    }

    /**
     * @inheritdoc
     */
    public function withArguments(array $arguments = null): self
    {
        throw new \BadMethodCallException();
    }
    
    /**
     * @inheritdoc
     */
    public function getCaller()
    {
        throw new \BadMethodCallException();
    }

    /**
     * @inheritdoc
     */
    public function getMethod(): string
    {
        throw new \BadMethodCallException();
    }

    /**
     * @inheritdoc
     */
    public function getArguments(): array
    {
        throw new \BadMethodCallException();
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->toString;
    }
}
