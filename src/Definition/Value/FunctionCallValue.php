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
 * Value object representing '<name()>'.
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
     * @param string $name e.g. 'randomElement'
     * @param array  $arguments
     */
    public function __construct(string $name, array $arguments = [])
    {
        $this->name = $name;
        $this->arguments = deep_clone($arguments);
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array|null
     */
    public function getArguments()
    {
        return deep_clone($this->arguments);
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return [
            $this->name,
            $this->getArguments(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return sprintf('<%s(%s)>', $this->name, [] === $this->arguments ? '' : 'args');
    }
}
