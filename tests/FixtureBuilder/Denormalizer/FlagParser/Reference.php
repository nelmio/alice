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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser;

use Nelmio\Alice\Definition\Flag\ConfiguratorFlag;
use Nelmio\Alice\Definition\Flag\ElementFlag;
use Nelmio\Alice\Definition\Flag\ExtendFlag;
use Nelmio\Alice\Definition\Flag\OptionalFlag;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\ServiceReference\FixtureReference;

/**
 * List a list of strings containing flags with the expected result. The list is flagged to be able to easily extract
 * some parts of it. This class aims at centralising all the possible fixture tested to avoid too much duplication.
 */
final class Reference
{
    private static $references;

    private function __construct()
    {
        self::$references = [
            'element' => [
                'empty string' => [
                    '',
                    new FlagBag(''),
                ],
                'non-empty string without flags' => [
                    'something',
                    new FlagBag('something'),
                ],
                'string with 1 flag' => [
                    'user (dummy_flag)',
                    (new FlagBag('user'))->withFlag(new ElementFlag('dummy_flag')),
                ],
                'string with 1 flag and extra space' => [
                    'user ( dummy_flag )',
                    (new FlagBag('user'))->withFlag(new ElementFlag('dummy_flag')),
                ],
                'string with 2 flags' => [
                    'user (dummy_flag, another_dummy_flag)',
                    (new FlagBag('user'))
                        ->withFlag(new ElementFlag('dummy_flag'))
                        ->withFlag(new ElementFlag('another_dummy_flag'))
                    ,
                ],
                'with an index' => [
                    '0 (dummy_flag)',
                    (new FlagBag('0'))->withFlag(new ElementFlag('dummy_flag')),
                ],
                'with an index with no flags' => [
                    '0',
                    new FlagBag('0'),
                ],
                'with an numeric index' => [
                    0,
                    new FlagBag('0'),
                ],
            ],
            'malformed-element' => [
                'non-empty string with empty flags' => [
                    'user ()',
                    null,
                ],
                'non-empty string with empty flags with extra spaces' => [
                    ' user () ',
                    null,
                ],
                'non-empty string with empty flags with lot of extra spaces' => [
                    '  user ()  ',
                    null,
                ],

                'string with 1 flag and lot of extra space' => [
                    'user (  dummy_flag  )',
                    null,
                ],
                'string with 1 flag and ending with useless comma' => [
                    'user (dummy_flag,)',
                    null,
                ],
                'string with 1 flag and ending with useless comma and space' => [
                    'user (dummy_flag,  )',
                    null,
                ],
                'string with 1 flag and starting with useless comma' => [
                    'user (, dummy_flag)',
                    null,
                ],
                'string with 1 flag and starting with useless comma and space' => [
                    'user ( , dummy_flag)',
                    null,
                ],
                'string with 1 flag and starting with useless comma and a lot of spaces' => [
                    'user (  , dummy_flag)',
                    null,
                ],
                'string with 1 flag and starting with useless comma and extra spaces after comma' => [
                    'user (,  dummy_flag)',
                    null,
                ],

                'string with 2 flags and lot of extra space' => [
                    'user (  dummy_flag  ,  another_dummy_flag  )',
                    null,
                ],
                'string with 2 flags and ending with useless comma' => [
                    'user (dummy_flag, another_dummy_flag,)',
                    null,
                ],
                'string with 2 flag and ending with useless comma and space' => [
                    'user (dummy_flag, another_dummy_flag,  )',
                    null,
                ],
                'string with 2 flags and starting with useless comma' => [
                    'user (, dummy_flag, another_dummy_flag)',
                    null,
                ],
                'string with 2 flags and starting with useless comma and space' => [
                    'user ( , dummy_flag, another_dummy_flag)',
                    null,
                ],
                'string with 2 flags and starting with useless comma and a lot of spaces' => [
                    'user (  , dummy_flag, another_dummy_flag)',
                    null,
                ],
                'string with 2 flags and starting with useless comma and extra spaces after comma' => [
                    'user (,  dummy_flag, another_dummy_flag)',
                    null,
                ],
            ],
            'extend' => [
                'with 1 extend' => [
                    'extends user_base',
                    (new FlagBag(''))
                        ->withFlag(new ExtendFlag(new FixtureReference('user_base')))
                    ,
                ],
            ],
            'malformed-extend' => [
                'extend without space' => [
                    'extendsuser_base',
                    null,
                ],
            ],
            'optional' => [
                'nominal' => [
                    '60%?',
                    (new FlagBag(''))->withFlag(new OptionalFlag(60)),
                ],
            ],
            'malformed-optional' => [
                'with negative number' => [
                    '-60%?',
                    null,
                ],
                'with float' => [
                    '.6%?',
                    null,
                ],
                'without percentile character' => [
                    '60?',
                    null,
                ],
                'without question mark character' => [
                    '60%',
                    null,
                ],
            ],
            'template' => [
                'nominal' => [
                    'template',
                    (new FlagBag(''))->withFlag(new TemplateFlag()),
                ],
            ],
            'unique' => [
                'nominal' => [
                    'unique',
                    (new FlagBag(''))->withFlag(new UniqueFlag()),
                ],
            ],
            'configurator' => [
                'nominal' => [
                    'configurator',
                    (new FlagBag(''))->withFlag(new ConfiguratorFlag()),
                ],
            ],
        ];
    }

    public static function getElements()
    {
        return self::getList('element');
    }

    public static function getMalformedElements()
    {
        return self::getList('malformed-element');
    }

    public static function getExtends()
    {
        return self::getList('extend');
    }

    public static function getMalformedExtends()
    {
        return self::getList('malformed-extend');
    }

    public static function getOptionals()
    {
        return self::getList('optional');
    }

    public static function getMalformedOptionals()
    {
        return self::getList('malformed-optional');
    }

    public static function getTemplates()
    {
        return self::getList('template');
    }

    public static function getUniques()
    {
        return self::getList('unique');
    }

    public static function getConfigurators()
    {
        return self::getList('configurator');
    }

    private static function getList(string $name): array
    {
        if (null === self::$references) {
            new self();
        }

        return self::$references[$name];
    }
}
