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

use Nelmio\Alice\FileLoaderInterface;
use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectSet;
use Nelmio\Alice\Throwable\CacheKeyGenerationThrowable;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Leverage a cache layer to speed up loading time.
 */
final class PsrCacheFileLoader implements FileLoaderInterface
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
        FileLoaderInterface $decoratedLoader,
        FileCacheKeyGeneratorInterface $fileCacheKeyGenerator,
        CacheItemPoolInterface $cacheItemPool
    ) {
        $this->loader = $decoratedLoader;
        $this->fileCacheKeyGenerator = $fileCacheKeyGenerator;
        $this->cacheItemPool = $cacheItemPool;
    }

    /**
     * @inheritdoc
     */
    public function loadFile(string $file, array $parameters = [], array $objects = []): ObjectSet
    {
        try {
            $cacheKey = $this->fileCacheKeyGenerator->generateForFile($file, $parameters, $objects);
        } catch (CacheKeyGenerationThrowable $throwable) {
            return $this->loader->loadFile($file, $parameters, $objects);
        }

        $cachedSet = $this->cacheItemPool->getItem($cacheKey);
        if ($cachedSet->isHit()) {
            return $cachedSet->get();
        }

        $objectSet = $this->loader->loadFile($file, $parameters, $objects);
        $cachedSet->set($objectSet);
        $this->cacheItemPool->save($cachedSet);

        return $objectSet;
    }
}
