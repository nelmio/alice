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

/**
 * Contain a string value that can be processed, i.e. is an expression that can be resolved. E.g. '$username'.
 */
final class ProcessableValue implements ValueInterface
{
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->value;
    }
}
