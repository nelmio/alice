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

use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\Loader\FileCacheKeyGeneratorInterface;
use Nelmio\Alice\Throwable\Exception\FileLocator\FileNotFoundException;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

final class Sha1FileCacheKeyGenerator implements FileCacheKeyGeneratorInterface
{
    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
    }

    /**
     * @inheritdoc
     */
    public function generateForFile(string $file, array $parameters, array $objects): string
    {
        try {
            $realPath = $this->fileLocator->locate($file);
        } catch (FileNotFoundException $exception) {
            throw InvalidArgumentExceptionFactory::createForFileCouldNotBeFound($file, 0, $exception);
        }

        return sprintf(
            '%s%s%s',
            sha1_file($realPath),
            sha1(serialize($parameters)),
            sha1(serialize($objects))
        );
    }
}
