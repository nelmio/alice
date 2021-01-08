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

use function Nelmio\Alice\deep_clone;
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

    public function getArguments(): array
    {
        return deep_clone($this->arguments);
    }
    
    public function getValue()
    {
        return [
            $this->name,
            $this->getArguments(),
        ];
    }
    
    public function __toString(): string
    {
        return sprintf('<%s(%s)>', $this->name, [] === $this->arguments ? '' : var_export($this->arguments, true));
    }
}
