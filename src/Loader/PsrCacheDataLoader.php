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

use Nelmio\Alice\DataLoaderInterface;
use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Throwable\CacheKeyGenerationThrowable;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Leverage a cache layer to speed up loading time.
 */
final class PsrCacheDataLoader implements DataLoaderInterface
{
    use IsAServiceTrait;

    /**
     * @var CacheItemPoolInterface
     */
    private $cacheItemPool;

    /**
     * @var FileLocatorInterface
     */
    private $fileCacheKeyGenerator;

    /**
     * @var FileLoaderInterface
     */
    private $loader;

    public function __construct(
        DataLoaderInterface $decoratedLoader,
        DataCacheKeyGeneratorInterface $fileCacheKeyGenerator,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->loader = $decoratedLoader;
        $this->fileCacheKeyGenerator = $fileCacheKeyGenerator;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @inheritdoc
     */
    public function loadData(array $data, array $parameters = [], array $objects = []): ObjectSet
    {
        try {
            $cacheKey = $this->fileCacheKeyGenerator->generateForData($data, $parameters, $objects);
        } catch (CacheKeyGenerationThrowable $throwable) {
            return $this->loader->loadData($data, $parameters, $objects);
        }

        $cachedSet = $this->cacheItemPool->getItem($cacheKey);
        if ($cachedSet->isHit()) {
            return $cachedSet->get();
        }

        $objectSet = $this->loader->loadData($data, $parameters, $objects);
        $cachedSet->set($objectSet);
        $this->cacheItemPool->save($cachedSet);

        return $objectSet;
    }
}
