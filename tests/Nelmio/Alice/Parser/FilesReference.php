<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Parser;

use Nelmio\Alice\Parser\Chainable\PhpParser;
use Nelmio\Alice\Parser\Chainable\YamlParser;

/**
 * List a list of file names for Parsers. The list is flagged to be able to easily extract some parts of it. This class
 * aims at centralising all the possible fixture tested to avoid too much duplication.
 */
final class FilesReference
{
    public static $references;

    public function __construct()
    {
        /*
         * The format is as follows:
         * - first key is the group name
         * - keys within the group name are the element description
         * - the first element is the tested file name
         * - the second element is an array of parser class supporting this file
         */
        self::$references = [
            'php' => [
                'regular PHP file' => [
                    'dummy.php',
                    [PhpParser::class],
                ],
                'PHP file with uppercase extension' => [
                    'dummy.PHP',
                    [PhpParser::class],
                ],
                'regular PHP4 file' => [
                    'dummy.php4',
                    [],
                ],
                'regular PHP5 file' => [
                    'dummy.php5',
                    [],
                ],
                'regular PHP7 file' => [
                    'dummy.php7',
                    [PhpParser::class],
                ],
                'phpfied YAML file' => [
                    'dummy.yml.php',
                    [PhpParser::class],
                ],
                'remote php file with HTTP' => [
                    'http://example.com/dummy.php',
                    [],
                ],
                'remote php file with HTTPS' => [
                    'https://example.com/dummy.php',
                    [],
                ],
                'remote php file with FTP' => [
                    'ftp://user:password@example.com/dummy.php',
                    [],
                ],
                'remote php file with FTPS' => [
                    'ftps://user:password@example.com/dummy.php',
                    [],
                ],
            ],
            'yaml' => [
                'regular YAML file' => [
                    'dummy.yml',
                    [YamlParser::class],
                ],
                'regular YAML file with alternative extension' => [
                    'dummy.yaml',
                    [YamlParser::class],
                ],
                'regular YAML file with uppercase extension' => [
                    'dummy.YML',
                    [YamlParser::class],
                ],
                'regular YAML file with alternative extension in uppercase' => [
                    'dummy.YAML',
                    [YamlParser::class],
                ],
                'remote YAML file with HTTP' => [
                    'http://example.com/dummy.yml',
                    [],
                ],
                'remote YAML file with HTTPS' => [
                    'https://example.com/dummy.yml',
                    [],
                ],
                'remote YAML file with FTP' => [
                    'ftp://user:password@example.com/dummy.yml',
                    [],
                ],
                'remote YAML file with FTPS' => [
                    'ftps://user:password@example.com/dummy.yml',
                    [],
                ],
            ],
            'unsupported' => [
                'XML file' => ['dummy.xml'],
                'CSV file' => ['dummy.csv'],
            ]
        ];
    }

    public static function getPhpList(): array
    {
        return self::getList('php');
    }

    public static function getYamlList(): array
    {
        return self::getList('yaml');
    }

    public static function getUnsupportedList(): array
    {
        return self::getList('unsupported');
    }

    public static function getList(string $name): array
    {
        if (null === self::$references) {
            new self();
        }

        return self::$references[$name];
    }
}
