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

final class OptionalValue implements ValueInterface
{
    /**
     * @var float|int|ValueInterface
     */
    private $quantifier;

    /**
     * @var ValueInterface|string
     */
    private $firstMember;
    
    /**
     * @var ValueInterface|null|string
     */
    private $secondMember;

    /**
     * @param ValueInterface|int|float   $quantifier
     * @param ValueInterface|string      $firstMember
     * @param ValueInterface|string|null $secondMember
     */
    public function __construct($quantifier, $firstMember, $secondMember)
    {
        $this->quantifier = $quantifier;
        $this->firstMember = $firstMember;
        $this->secondMember = $secondMember;
    }

    /**
     * @return float|int|ValueInterface
     */
    public function getQuantifier()
    {
        return $this->quantifier;
    }

    /**
     * @return string|ValueInterface
     */
    public function getFirstMember()
    {
        return $this->firstMember;
    }
    
    public function getSecondMember()
    {
        return $this->secondMember;
    }

    /**
     * {@inheritdoc}
     * 
     * @return array The first element is the quantifier and the second the elements.
     */
    public function getValue(): array
    {
        return [$this->quantifier, $this->firstMember, $this->secondMember];
    }
}
