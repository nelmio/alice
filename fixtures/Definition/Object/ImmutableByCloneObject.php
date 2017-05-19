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

class ImmutableByCloneObject implements ObjectInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var object
     */
    private $instance;

    /**
     * @param string $id
     * @param object $instance
     */
    public function __construct(string $id, $instance)
    {
        $this->id = $id;
        $this->instance = $instance;
    }

    /**
     * @inheritdoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @inheritdoc
     */
    public function withInstance($newInstance)
    {
        return new self($this->id, $newInstance);
    }

    public function __clone()
    {
        $this->instance = clone $this->instance;
    }
}
