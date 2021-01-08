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
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

final class UniqueValue implements ValueInterface
{
    /**
     * @var string
     */
    private $id;

    
    private $value;

    /**
     * @param string $id Unique across a fixture set, is used to generate unique values.
     */
    public function __construct(string $id, $value)
    {
        $this->id = $id;

        if ($value instanceof self) {
            throw InvalidArgumentExceptionFactory::createForRedundantUniqueValue($id);
        }

        $this->value = deep_clone($value);
    }

    public function withValue($value): self
    {
        return new self($this->id, $value);
    }

    public function getId(): string
    {
        return $this->id;
    }
    
    public function getValue()
    {
        return deep_clone($this->value);
    }
    
    public function __toString(): string
    {
        return sprintf(
            '(unique) %s',
            $this->value instanceof ValueInterface
                ? $this->value
                : var_export($this->value, true)
        );
    }
}
