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
 * Value object representing "@user0->username".
 */
final class FixturePropertyValue implements ValueInterface
{
    /**
     * @var ValueInterface
     */
    private $reference;

    /**
     * @var string
     */
    private $property;

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

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return sprintf('%s->%s', $this->reference, $this->property);
    }
}
