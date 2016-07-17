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
 * VO representing '<name()>'.
 */
final class FunctionCallValue implements ValueInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param string     $name e.g. 'randomElement'
     * @param array|null $arguments
     */
    public function __construct(string $name, array $arguments = null)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return [
            $this->name,
            $this->arguments,
        ];
    }
}
