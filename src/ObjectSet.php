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

namespace Nelmio\Alice;

/**
 * Value object containing the parameters and objects built from the loaded and injected ones.
 */
final class ObjectSet
{
    /**
     * @var array Keys are parameter keys (strings) and values can be anything.
     */
    private $parameters;

    /**
     * @var object[] Keys are objects IDs (strings)
     */
    private $objects;

    public function __construct(ParameterBag $parameters, ObjectBag $objects)
    {
        $this->parameters = $parameters->toArray();
        $this->objects = $objects->toArray();
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getObjects(): array
    {
        return $this->objects;
    }
}
