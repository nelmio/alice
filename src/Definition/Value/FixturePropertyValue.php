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
 * Value object representing "@user0->username".
 */
final class FixturePropertyValue implements ValueInterface
{
    /**
     * @var FixtureReferenceValue
     */
    private $reference;

    /**
     * @var string
     */
    private $property;

    /**
     * @param ValueInterface $reference e.g. 'user0'
     * @param string         $property  e.g. 'username'
     */
    public function __construct(ValueInterface $reference, string $property)
    {
        $this->reference = $reference;
        $this->property = $property;
    }

    public function getReference(): ValueInterface
    {
        return $this->reference;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * @inheritdoc
     */
    public function getValue(): array
    {
        return [
            $this->reference,
            $this->property,
        ];
    }
}
