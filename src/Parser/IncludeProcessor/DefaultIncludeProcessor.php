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

use function array_reverse;
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

        // The order the the include statements needs to be reversed. Indeed when merging two sets of data, e.g.
        // mergeInclude($data1, $data2), the elements of $data1 take precedence in case of conflict keys. And what
        // we want when we have:
        //
        // root (fixture file with the following include statements)
        //   - file1
        //   - file2
        //   - file3
        //
        // Is the data to be merged as follows:
        //
        // merge (
        //     rootData, <-- root data: is the one from which the data takes precedence over the included files
        //     merge(
        //         file3Data, <-- last included file: is the one from which the data takes precedence over the other already included files
        //         merge(
        //             file2Data,
        //             file1Data <-- first included so is the one which data can be overridden during the merge
        //         )
        //     )
        // )
        //
        $includeData = $this->retrieveIncludeData($parser, $file, array_reverse($include));

        $this->included = [];

        return $this->dataMerger->mergeInclude($data, $includeData);
    }

    private function retrieveIncludeData(ParserInterface $parser, string $file, array $include): array
    {
        $data = [];

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

        return $data;
    }
}
