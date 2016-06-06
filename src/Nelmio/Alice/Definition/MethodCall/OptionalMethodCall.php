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

use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\MethodCallInterface;

final class OptionalMethodCall implements MethodCallInterface
{
    /**
     * @var MethodCallInterface
     */
    private $methodCall;

    /**
     * @var OptionalFlag
     */
    private $flag;

    /**
     * @param MethodCallInterface $methodCall
     * @param OptionalFlag        $flag
     */
    public function __construct(MethodCallInterface $methodCall, OptionalFlag $flag)
    {
        $this->methodCall = $methodCall;
        $this->flag = $flag;
    }

    /**
     * @inheritdoc
     */
    public function getCaller()
    {
        return $this->methodCall->getCaller();
    }

    /**
     * @inheritdoc
     */
    public function getMethod(): string
    {
        return $this->methodCall->getMethod();
    }

    /**
     * @inheritdoc
     */
    public function getArguments(): array
    {
        return $this->methodCall->getArguments();
    }

    /**
     * @return int Element of ]0;100[.
     */
    public function getPercentage(): int
    {
        return $this->flag->getPercentage();
    }
}
