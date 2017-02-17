<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Nelmio\Alice\Loader\CacheKeyGenerator;

use Nelmio\Alice\Loader\DataCacheKeyGeneratorInterface;

final class Sha1DataCacheKeyGenerator implements DataCacheKeyGeneratorInterface
{
    /**
     * @var int|null
     */
    private $seed;

    public function __construct(int $seed = null)
    {
        $this->seed = $seed;
    }

    /**
     * @inheritdoc
     */
    public function generateForData(array $data, array $parameters, array $objects): string
    {
        return sprintf(
            '%s%s%s',
            sha1(serialize($data)),
            sha1(serialize($parameters)),
            sha1(serialize($objects)),
            (string) $this->seed
        );
    }
}
