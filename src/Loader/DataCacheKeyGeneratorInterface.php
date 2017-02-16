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

namespace Nelmio\Alice\Loader;

use Nelmio\Alice\Throwable\CacheKeyGenerationThrowable;

interface DataCacheKeyGeneratorInterface
{
    /**
     * @param array $data
     * @param array $parameters
     * @param array $objects
     *
     * @throws CacheKeyGenerationThrowable
     *
     * @return string cache key
     */
    public function generateForData(array $data, array $parameters, array $objects): string;
}
