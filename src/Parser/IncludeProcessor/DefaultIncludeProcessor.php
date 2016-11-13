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

use Nelmio\Alice\Exception\InvalidArgumentExceptionFactory;
use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\Parser\IncludeProcessorInterface;
use Nelmio\Alice\ParserInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Error\TypeErrorFactory;

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

        foreach ($include as $includeFile) {
            if (false === is_string($includeFile)) {
                throw TypeErrorFactory::createForInvalidIncludedFilesInData($includeFile, $file);
            }

            if (0 === strlen($includeFile)) {
                throw InvalidArgumentExceptionFactory::createForEmptyIncludedFileInData($file);
            }

            $filePathToInclude = $this->fileLocator->locate($includeFile, dirname($file));
            $fileToIncludeData = $parser->parse($filePathToInclude);
            if (array_key_exists('include', $fileToIncludeData)) {
                $fileToIncludeData = $this->process($parser, $file, $fileToIncludeData);
            }

            $data = $this->dataMerger->mergeInclude($data, $fileToIncludeData);
        }

        return $data;
    }
}
