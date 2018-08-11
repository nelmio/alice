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

namespace Nelmio\Alice\Parser\IncludeProcessor;

use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Parser\IncludeProcessorInterface;
use Nelmio\Alice\ParserInterface;
use Nelmio\Alice\Throwable\Error\TypeErrorFactory;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;

final class DefaultIncludeProcessor implements IncludeProcessorInterface
{
    use IsAServiceTrait;

    /**
     * @var IncludeDataMerger
     */
    private $dataMerger;

    /**
     * @var FileLocatorInterface
     */
    private $fileLocator;

    /**
     * @var array Array used to keep track of the included files to avoid including the same file twice which could
     *            cause infinite loops.
     */
    private $included = [];

    public function __construct(FileLocatorInterface $fileLocator)
    {
        $this->fileLocator = $fileLocator;
        $this->dataMerger = new IncludeDataMerger();
    }

    /**
     * @inheritdoc
     */
    public function process(ParserInterface $parser, string $file, array $data): array
    {
        $file = $this->fileLocator->locate($file);

        if (false === array_key_exists('include', $data)) {
            throw InvalidArgumentExceptionFactory::createForNoIncludeStatementInData($file);
        }

        $include = $data['include'];
        unset($data['include']);

        if (null === $include) {
            return $data;
        }

        if (false === is_array($include)) {
            throw TypeErrorFactory::createForInvalidIncludeStatementInData($include, $file);
        }

        if (array_key_exists($file, $this->included)) {
            return $data;
        }

        $this->included[$file] = true;

        foreach ($include as $includeFile) {
            if (false === is_string($includeFile)) {
                throw TypeErrorFactory::createForInvalidIncludedFilesInData($includeFile, $file);
            }

            if (0 === strlen($includeFile)) {
                throw InvalidArgumentExceptionFactory::createForEmptyIncludedFileInData($file);
            }

            if (array_key_exists($includeFile, $this->included)) {
                continue;
            }

            $filePathToInclude = $this->fileLocator->locate($includeFile, dirname($file));

            $fileToIncludeData = $parser->parse($filePathToInclude);

            if (array_key_exists('include', $fileToIncludeData)) {
                $fileToIncludeData = $this->process($parser, $includeFile, $fileToIncludeData);
            }

            $data = $this->dataMerger->mergeInclude($data, $fileToIncludeData);

            $this->included[$includeFile] = true;
        }

        $this->included = [];

        return $data;
    }
}
