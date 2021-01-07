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
    
    public function __construct(string $id, object $instance)
    {
        $this->id = $id;
        $this->instance = $instance;
    }
    
    public function getId(): string
    {
        return $this->id;
    }
    
    public function getInstance()
    {
        return $this->instance;
    }
    
    public function withInstance(object $newInstance)
    {
        return new self($this->id, $newInstance);
    }

    public function __clone()
    {
        $this->instance = clone $this->instance;
    }
}
