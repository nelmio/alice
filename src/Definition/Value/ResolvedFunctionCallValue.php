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

namespace Nelmio\Alice\Definition\Value;

use Nelmio\Alice\Definition\ValueInterface;

/**
 * Value object representing a function arguments which have already been re-solved.
 */
final class ResolvedFunctionCallValue implements ValueInterface
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
        $this->arguments = $arguments;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): array
    {
        return $this->arguments;
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
        return sprintf('<%s(%s)>', $this->name, [] === $this->arguments ? '' : var_export($this->arguments, true));
    }
}
