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

namespace Nelmio\Alice\Parser\Chainable;

use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Parser\ChainableParserInterface;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Parser\ParseExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Parser\UnparsableFileException;
use Symfony\Component\Yaml\Exception\ParseException as SymfonyParseException;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;
use Symfony\Component\Yaml\Yaml;

final class YamlParser implements ChainableParserInterface
{
    use IsAServiceTrait;

    /** @interval */
    const REGEX = '/.{1,}\.ya?ml$/i';

    /**
     * @var SymfonyYamlParser
     */
    private $yamlParser;

    public function __construct(SymfonyYamlParser $yamlParser)
    {
        $this->yamlParser = $yamlParser;
    }

    /**
     * @inheritdoc
     */
    public function canParse(string $file): bool
    {
        if (false === stream_is_local($file)) {
            return false;
        }

        return 1 === preg_match(self::REGEX, $file);
    }

    /**
     * {@inheritDoc}
     *
     * @param string $file Local YAML file
     *
     * @throws UnparsableFileException
     */
    public function parse(string $file): array
    {
        if (false === file_exists($file)) {
            throw InvalidArgumentExceptionFactory::createForFileCouldNotBeFound($file);
        }

        try {
            $data = defined('Symfony\\Component\\Yaml\\Yaml::PARSE_CONSTANT')
                ? $this->yamlParser->parse(file_get_contents($file), Yaml::PARSE_CONSTANT)
                : $this->yamlParser->parse(file_get_contents($file));

            // $data is null only if the YAML file was empty; otherwise an exception is thrown
            return (null === $data) ? [] : $data;
        } catch (\Exception $exception) {
            if ($exception instanceof SymfonyParseException) {
                throw ParseExceptionFactory::createForInvalidYaml($file, 0, $exception);
            }

            throw ParseExceptionFactory::createForUnparsableFile($file, 0, $exception);
        }
    }
}
