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
 * VO representing "@user0".
 */
final class FixtureReferenceValue implements ValueInterface
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @param string $reference e.g. "user0"
     */
    public function __construct(string $reference)
    {
        $this->reference = $reference;
    }

    /**
     * @inheritdoc
     */
    public function getValue(): string
    {
        return $this->reference;
    }
}
