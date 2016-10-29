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

    /**
     * @inheritdoc
     */
    public function withArguments(array $arguments = null)
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
        $this->__call(__METHOD__, func_get_args());
    }
}
