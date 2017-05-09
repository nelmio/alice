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

namespace Nelmio\Alice\Entity;

class DummyWithImmutableFunction
{
    private $val;

    public function __construct(string $val)
    {
        $this->val = $val;
    }

    public function withVal(string $val): self
    {
        return new self($val);
    }

    public function getVal(): string
    {
        return $this->val;
    }
}
