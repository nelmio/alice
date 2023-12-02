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
use Nelmio\Alice\Definition\ServiceReferenceInterface;
use Nelmio\Alice\NotCallableTrait;

class MutableMethodCall implements MethodCallInterface
{
    use NotCallableTrait;

    /**
     * @var ServiceReferenceInterface|null
     */
    private $caller;

    private $method;

    /**
     * @var array|null
     */
    private $arguments;

    public function __construct(?ServiceReferenceInterface $caller, $method, ?array $arguments = null)
    {
        $this->caller = $caller;
        $this->method = $method;
        $this->arguments = $arguments;
    }

    public function withArguments(?array $arguments = null): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getCaller()
    {
        return $this->caller;
    }

    public function setCaller(?ServiceReferenceInterface $caller = null): void
    {
        $this->caller = $caller;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod($method): void
    {
        $this->method = $method;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function setArguments(?array $arguments = null): void
    {
        $this->arguments = $arguments;
    }

    public function __toString(): string
    {
        return 'mutable_method_call';
    }
}
