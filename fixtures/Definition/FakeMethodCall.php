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

namespace Nelmio\Alice\Definition;

use Nelmio\Alice\NotCallableTrait;

class FakeMethodCall implements MethodCallInterface
{
    use NotCallableTrait;

    public function withArguments(?array $arguments = null): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getCaller(): void
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getMethod(): string
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function getArguments(): array
    {
        $this->__call(__METHOD__, func_get_args());
    }

    public function __toString(): string
    {
        $this->__call(__METHOD__, func_get_args());
    }
}
