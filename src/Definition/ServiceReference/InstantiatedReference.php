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

namespace Nelmio\Alice\Definition\ServiceReference;

use Nelmio\Alice\Definition\ServiceReferenceInterface;

/**
 * Value object to point to refer to a "service", e.g. 'nelmio.alice.user_factory'. Is used in some bridges to be able
 * to make use of existing factories or simply an existing fixture than can be used as a constructor.
 */
final class InstantiatedReference implements ServiceReferenceInterface
{
    /**
     * @var string
     */
    private $id;

    public function __construct(string $serviceId)
    {
        $this->id = $serviceId;
    }

    /**
     * @return string Service ID coming from a framework DIC or an instantiated fixture e.g. 'nelmio.alice.user_factory'
     */
    public function getId(): string
    {
        return $this->id;
    }
}
