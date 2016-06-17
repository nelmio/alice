<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\ServiceReference;

use Nelmio\Alice\Definition\ServiceReferenceInterface;
use Nelmio\Alice\FixtureInterface;

/**
 * Value object to point to refer to a fixture. The reference can be relative, e.g. 'user_base' (fixture reference) or
 * absolute e.g. 'Nelmio\Alice\User#user_base' (fixture ID).
 */
final class FixtureReference implements ServiceReferenceInterface
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @param string $reference
     */
    public function __construct(string $reference)
    {
        $this->reference = $reference;
    }

    /**
     * A fixture reference may be relative, e.g. 'user_base' (fixture reference). By giving it a fixture, this method
     * creates a new reference which will be absolute, e.g. 'Nelmio\Alice\User#user_base' (fixture ID.
     *
     * @param FixtureInterface $fixture
     *
     * @return self
     */
    public function createAbsoluteFrom(FixtureInterface $fixture): self
    {
        if (false !== strpos($this->reference, '#')) {
            throw new \BadMethodCallException(
                sprintf(
                    'Attempted to make the reference "%s" absolute from the fixture of ID "%s", however the reference '.
                    'is already absolute.',
                    $this->reference,
                    $fixture->getId()
                )
            );
        }
        
        $clone = clone $this;
        $clone->reference = $fixture->getClassName().'#'.$this->reference;

        return $clone;
    }

    public function getReference(): string
    {
        return $this->reference;
    }
}
