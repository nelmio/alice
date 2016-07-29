<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Definition\Object;

use Nelmio\Alice\ObjectInterface;

/**
 * Minimalist implementation of ObjectInterface.
 */
final class SimpleObject implements ObjectInterface
{
    /**
     * @var string
     */
    private $reference;

    /**
     * @var object
     */
    private $instance;

    /**
     * @param string  $reference
     * @param object $instance
     */
    public function __construct(string $reference, $instance)
    {
        if (false === is_object($instance)) {
            throw new \TypeError(
                sprintf(
                    'Expected instance argument to be an object, got "%s" instead.',
                    gettype($instance)
                )
            );
        }
        
        $this->reference = $reference;
        $this->instance = clone $instance;
    }

    /**
     * @param object $newInstance
     *
     * @return self
     */
    public function withInstance($newInstance): self
    {
        return new self($this->reference, $newInstance);
    }

    /**
     * @inheritdoc
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @inheritdoc
     */
    public function getInstance()
    {
        return clone $this->instance;
    }
}
