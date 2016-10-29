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

final class EvaluatedValue implements ValueInterface
{
    /**
     * @var
     */
    private $expression;

    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * {@inheritdoc}
     *
     * @return array The first element is the quantifier and the second the elements.
     */
    public function getValue(): string
    {
        return $this->expression;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string
    {
        return $this->expression;
    }
}
