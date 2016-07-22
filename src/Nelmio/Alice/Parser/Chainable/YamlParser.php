<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Parser\Chainable;

use Nelmio\Alice\Exception\FixtureBuilder\Parser\ParseException;
use Nelmio\Alice\Parser\ChainableParserInterface;
use Nelmio\Alice\NotClonableTrait;
use Symfony\Component\Yaml\Exception\ParseException as SymfonyParseException;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;

final class YamlParser implements ChainableParserInterface
{
    use NotClonableTrait;

    /**
     * @var SymfonyYamlParser
     */
    private $yamlParser;

    public function __construct(SymfonyYamlParser $yamlParser)
    {
        $this->yamlParser = $yamlParser;
    }

    /**
     * {@inheritDoc}
     *
     * @param string $file Local YAML file
     *
     * @throws ParseException
     */
    public function parse(string $file): array
    {
        if (false === file_exists($file)) {
            throw new \InvalidArgumentException(sprintf('File "%s" could not be found.', $file));
        }

        try {
            $data = $this->yamlParser->parse(file_get_contents($file));

            // $data is null only if the YAML file was empty; otherwise an exception is thrown
            return (null === $data) ? [] : $data;
        } catch (SymfonyParseException $exception) {
            throw new ParseException(
                sprintf('The file "%s" does not contain valid YAML.', $file),
                0,
                $exception
            );
        } catch (\Exception $error) {
            throw new ParseException(
                sprintf('Could not parse the file "%s".', $file),
                0,
                $error
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function canParse(string $file): bool
    {
        if (false === stream_is_local($file)) {
            return false;
        }

        return 1 === preg_match('/.{1,}\.ya?ml$/i', $file);
    }
}
