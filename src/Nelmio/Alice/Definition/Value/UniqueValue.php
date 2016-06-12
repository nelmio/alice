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

final class UniqueValue implements ValueInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $id Unique across a fixture set, is used to generate unique values.
     * @param mixed  $value
     */
    public function __construct(string $id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return (is_object($this->value))? clone $this->value : $this->value;
    }
}
