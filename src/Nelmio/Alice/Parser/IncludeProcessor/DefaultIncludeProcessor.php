<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Parser\IncludeProcessor;

use Nelmio\Alice\Exception\Parser\InvalidArgumentException;
use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\Parser\IncludeProcessorInterface;
use Nelmio\Alice\ParserInterface;

final class DefaultIncludeProcessor implements IncludeProcessorInterface
{
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
            throw new InvalidArgumentException(
                sprintf(
                    'Could not find any include statement in the file "%s".',
                    $file
                )
            );
        }

        $include = $data['include'];
        unset($data['include']);

        if (null === $include) {
            return $data;
        }

        if (false === is_array($include)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Expected include statement to be either null or an array of files to include. Got %s instead in '
                    .'file "%s".',
                    gettype($include),
                    $file
                )
            );
        }

        foreach ($include as $includeFile) {
            if (false === is_string($includeFile)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Expected elements of include statement to be file names. Got %s instead in file "%s".',
                        gettype($includeFile),
                        $file
                    )
                );
            }

            if (0 === strlen($includeFile)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Expected elements of include statement to be file names. Got empty string instead in file '
                        .'"%s".',
                        $file
                    )
                );
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

    public function __clone()
    {
        throw new \DomainException('Is not clonable');
    }
}
