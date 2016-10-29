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

namespace Nelmio\Alice\Definition\MethodCall;

use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\NotCallableTrait;

final class DummyMethodCall implements MethodCallInterface
{
    use NotCallableTrait;

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
        $this->__call(__METHOD__, func_get_args());
    }
    
    /**
     * @inheritdoc
     */
    public function getCaller()
    {
        $this->__call(__METHOD__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getMethod(): string
    {
        $this->__call(__METHOD__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function getArguments(): array
    {
        $this->__call(__METHOD__, func_get_args());
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->toString;
    }
}
