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
use Nelmio\Alice\Throwable\Error\TypeErrorFactory;

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
     * @param object $instance
     */
    public function __construct(string $id, $instance)
    {
        if (false === is_object($instance)) {
            throw TypeErrorFactory::createForObjectArgument($instance);
        }
        
        $this->reference = $id;
        $this->instance = $instance;
    }

    /**
     * @inheritdoc
     */
    public function withInstance($newInstance): self
    {
        $clone = clone $this;
        $clone->instance = $newInstance;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->reference;
    }

    /**
     * @inheritdoc
     */
    public function getInstance()
    {
        return $this->instance;
    }
}
