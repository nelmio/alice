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
 * Value object representing "@user0->getUserName()"
 */
final class FixtureMethodCallValue implements ValueInterface
{
    /**
     * @var ValueInterface
     */
    private $reference;

    /**
     * @var FunctionCallValue
     */
    private $function;

    public function __construct(ValueInterface $reference, FunctionCallValue $function)
    {
        $this->reference = $reference;
        $this->function = $function;
    }

    public function getReference(): ValueInterface
    {
        return $this->reference;
    }

    public function getFunctionCall(): FunctionCallValue
    {
        return $this->function;
    }
    
    public function getValue(): array
    {
        return [
            $this->reference,
            $this->function,
        ];
    }
    
    public function __toString(): string
    {
        return sprintf(
            '%s->%s(%s)',
            $this->reference,
            $this->function->getName(),
            [] === $this->function->getArguments() ? '' : var_export($this->function->getArguments(), true)
        );
    }
}
