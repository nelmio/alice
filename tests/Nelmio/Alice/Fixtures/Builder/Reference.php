<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Builder;

use Nelmio\Alice\Fixtures\Fixture;

/**
 * List a list of fixture reference, with the expected result. The list is flagged to be able to easily extract some
 * parts of it. This class aims at centralising all the possible fixture tested to avoid too much duplication.
 */
final class Reference
{
    private static $references;

    private function __construct()
    {
        self::$references = [
            'simple' => [
                'simple' => [
                    'user',
                    [
                        FixtureFactory::create('user', null),
                    ],
                ],
                'simple with special allowed characters' => [
                    'user100.or_something/else',
                    [
                        FixtureFactory::create('user100.or_something/else', null),
                    ]
                ],
                'simple with flag' => [
                    'user (dummy_flag)',
                    [
                        FixtureFactory::create('user (dummy_flag)', null),
                    ],
                ],
            ],
            'list' => [
                'nominal' => [
                    'user_{alice, bob}',
                    [
                        FixtureFactory::create('user_alice', 'alice'),
                        FixtureFactory::create('user_bob', 'bob'),
                    ],
                ],
                'nominal with flag' => [
                    'user_{alice, bob} (dummy_flag)',
                    [
                        FixtureFactory::create('user_alice (dummy_flag)', 'alice'),
                        FixtureFactory::create('user_bob (dummy_flag)', 'bob'),
                    ],
                ],
                'nominal with three elements' => [
                    'user_{alice, bob, steve}',
                    [
                        FixtureFactory::create('user_alice', 'alice'),
                        FixtureFactory::create('user_bob', 'bob'),
                        FixtureFactory::create('user_steve', 'steve'),
                    ],
                ],
                'nominal with digits' => [
                    'user_{0, 1}',
                    [
                        FixtureFactory::create('user_0', '0'),
                        FixtureFactory::create('user_1', '1'),
                    ],
                ],
                'nominal with special characters' => [
                    'user_{0./_, 1./_}',
                    [
                        FixtureFactory::create('user_0./_', '0./_'),
                        FixtureFactory::create('user_1./_', '1./_'),
                    ],
                ],
            ],
            'malformed-list' => [
                'with spaces at the beginning' => [
                    'user_{  alice, bob}',
                    [
                        FixtureFactory::create('user_alice', 'alice'),
                        FixtureFactory::create('user_bob', 'bob'),
                    ],
                ],
                'with spaces before comma' => [
                    'user_{alice  , bob}',
                    [
                        FixtureFactory::create('user_alice', 'alice'),
                        FixtureFactory::create('user_bob', 'bob'),
                    ],
                ],
                'with spaces after comma' => [
                    'user_{alice,   bob}',
                    [
                        FixtureFactory::create('user_alice', 'alice'),
                        FixtureFactory::create('user_bob', 'bob'),
                    ],
                ],
                'with spaces before ending curly brace' => [
                    'user_{alice, bob  }',
                    [
                        FixtureFactory::create('user_alice', 'alice'),
                        FixtureFactory::create('user_bob', 'bob'),
                    ],
                ],
                'with one comma at the end' => [
                    'user_{alice, bob,}',
                    [
                        FixtureFactory::create('user_alice', 'alice'),
                        FixtureFactory::create('user_bob', 'bob'),
                    ],
                ],
                'with one comma at the beginning' => [
                    'user_{, alice, bob}',
                    [
                        FixtureFactory::create('user_alice', 'alice'),
                        FixtureFactory::create('user_bob', 'bob'),
                    ],
                ],
                'with empty member with double quotes' => [
                    'user_{""}',
                    [
                        FixtureFactory::create('user_""', '""'),
                    ],
                ],
                'with empty member with single quotes' => [
                    'user_{\'\'}',
                    [
                        FixtureFactory::create('user_\'\'', '\'\''),
                    ],
                ],
            ],
            'segment' => [
                'nominal' => [
                    'user_{0..2}',
                    [
                        FixtureFactory::create('user_0', '0'),
                        FixtureFactory::create('user_1', '1'),
                        FixtureFactory::create('user_2', '2'),
                    ],
                ],
                'nominal with flag' => [
                    'user_{0..2} (dummy_flag)',
                    [
                        FixtureFactory::create('user_0 (dummy_flag)', '0'),
                        FixtureFactory::create('user_1 (dummy_flag)', '1'),
                        FixtureFactory::create('user_2 (dummy_flag)', '2'),
                    ],
                ],
                'only 1 value' => [
                    'user_{2..2}',
                    [
                        FixtureFactory::create('user_2', '2'),
                    ],
                ],
                'with inverted values' => [
                    'user_{2..0}',
                    [
                        FixtureFactory::create('user_0', '0'),
                        FixtureFactory::create('user_1', '1'),
                        FixtureFactory::create('user_2', '2'),
                    ],
                ],
            ],
            'segment-deprecated' => [
                'with three dots' => [
                    'user_{0...2}',
                    [
                        FixtureFactory::create('user_0', '0'),
                        FixtureFactory::create('user_1', '1'),
                    ],
                ],
                'with more than three dots' => [
                    'user_{0....2}',
                    [
                        FixtureFactory::create('user_0', '0'),
                        FixtureFactory::create('user_1', '1'),
                    ],
                ],
                'with three dots and flag ' => [
                    'user_{0...2} (dummy_flag)',
                    [
                        FixtureFactory::create('user_0 (dummy_flag)', '0'),
                        FixtureFactory::create('user_1 (dummy_flag)', '1'),
                    ],
                ],
                'with inverted values' => [
                    'user_{2...0}',
                    [
                        FixtureFactory::create('user_0', '0'),
                        FixtureFactory::create('user_1', '1'),
                    ],
                ],
                'only 1 value' => [
                    'user_{2...2}',
                    [],
                ],
            ],
            'malformed-segment' => [
                'with only one dot' => [
                    'user_{0.2}',
                    null,
                ],
                'with inverted values' => [
                    'user_{2..}',
                    null,
                ],
                'with negative value' => [
                    'user_{-1..2}',
                    null,
                ],
                'with inverted negative value' => [
                    'user_{2..-1}',
                    null,
                ],
                'with negative values' => [
                    'user_{-1...2}',
                    null,
                ],
                'with inverted negative values' => [
                    'user_{2...-1}',
                    null,
                ],
            ],
        ];
    }

    public static function getSimpleFixtures()
    {
        return self::getList('simple');
    }

    public static function getListFixtures()
    {
        return self::getList('list');
    }

    public static function getMalformedListFixtures()
    {
        return self::getList('malformed-list');
    }

    public static function getSegmentFixtures()
    {
        return self::getList('segment');
    }

    public static function getDeprecatedSegmentFixtures()
    {
        return self::getList('segment-deprecated');
    }

    public static function getMalformedSegmentFixtures()
    {
        return self::getList('malformed-segment');
    }

    /**
     * @param string $name
     *
     * @return array
     */
    private static function getList($name)
    {
        if (null === self::$references) {
            new self();
        }

        return self::$references[$name];
    }
}

final class FixtureFactory
{
    private function __construct()
    {
    }

    public static function create($name, $valueForCurrent)
    {
        return new Fixture(
            'Dummy',
            $name,
            [],
            $valueForCurrent
        );
    }
}
