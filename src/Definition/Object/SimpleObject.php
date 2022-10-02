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

    public function __construct(string $id, object $instance)
    {
        $this->reference = $id;
        $this->instance = $instance;
    }
    
    public function withInstance($newInstance): static
    {
        $clone = clone $this;
        $clone->instance = $newInstance;

        return $clone;
    }
    
    public function getId(): string
    {
        return $this->reference;
    }
    
    public function getInstance(): object
    {
        return $this->instance;
    }
}
