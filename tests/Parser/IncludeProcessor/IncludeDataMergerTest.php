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

/**
 * @covers Nelmio\Alice\FixtureBuilder\Parser\IncludeProcessor\IncludeDataMerger
 */
class IncludeDataMergerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IncludeDataMerger
     */
    private $merger;

    public function setUp()
    {
        $this->merger = new IncludeDataMerger();
    }

    public function testMergesParametersAndReturnTheResult()
    {
        $data = [
            'parameters' => [
                'foo' => 'oof',
                'bar' => 'rab',
            ],
        ];
        $include = [
            'parameters' => [
                'foo' => 'OOF',
                'white' => 'rabbit',
            ],
        ];
        $expected = [
            'parameters' => [
                'foo' => 'oof',
                'white' => 'rabbit',
                'bar' => 'rab',
            ],
        ];

        $actual = $this->merger->mergeInclude($data, $include);
        $this->assertSame($expected, $actual);
    }

    public function testCanMergeClassNames()
    {
        $data = [
            'Nelmio\Alice\Model\User' => [
                'user1' => [
                    'fullname' => 'Alice',
                ],
            ],
            'Nelmio\Alice\Model\Group' => [
                'group0' => [
                    'name' => 'Wonderland',
                ],
            ],
        ];
        $include = [
            'Nelmio\Alice\Model\User' => [
                'user0' => [
                    'fullname' => 'Bob',
                ],
            ],
            'Nelmio\Alice\Model\Group' => [
                'group0' => [
                    'name' => 'WondeRland',
                ],
            ],
            'Nelmio\Alice\Model\Author' => [
                'author0' => [
                    'fullname' => 'Charles Lutwidge Dodgson',
                ],
            ],
        ];
        $expected = [
            'Nelmio\Alice\Model\User' => [
                'user0' => [
                    'fullname' => 'Bob',
                ],
                'user1' => [
                    'fullname' => 'Alice',
                ],
            ],
            'Nelmio\Alice\Model\Group' => [
                'group0' => [
                    'name' => 'Wonderland',
                ],
            ],
            'Nelmio\Alice\Model\Author' => [
                'author0' => [
                    'fullname' => 'Charles Lutwidge Dodgson',
                ],
            ],
        ];

        $actual = $this->merger->mergeInclude($data, $include);
        $this->assertSame($expected, $actual);
    }

    public function testCanMergeCompleteDataSet()
    {
        $data = [
            'parameters' => [
                'foo' => 'oof',
                'bar' => 'rab',
            ],
            'Nelmio\Alice\Model\User' => [
                'user1' => [
                    'fullname' => 'Alice',
                ],
            ],
            'Nelmio\Alice\Model\Group' => [
                'group0' => [
                    'name' => 'Wonderland',
                ],
            ],
        ];
        $include = [
            'parameters' => [
                'foo' => 'OOF',
                'white' => 'rabbit',
            ],
            'Nelmio\Alice\Model\User' => [
                'user0' => [
                    'fullname' => 'Bob',
                ],
            ],
            'Nelmio\Alice\Model\Group' => [
                'group0' => [
                    'name' => 'WondeRland',
                ],
            ],
            'Nelmio\Alice\Model\Author' => [
                'author0' => [
                    'fullname' => 'Charles Lutwidge Dodgson',
                ],
            ],
        ];
        $expected = [
            'parameters' => [
                'foo' => 'oof',
                'white' => 'rabbit',
                'bar' => 'rab',
            ],
            'Nelmio\Alice\Model\User' => [
                'user0' => [
                    'fullname' => 'Bob',
                ],
                'user1' => [
                    'fullname' => 'Alice',
                ],
            ],
            'Nelmio\Alice\Model\Group' => [
                'group0' => [
                    'name' => 'Wonderland',
                ],
            ],
            'Nelmio\Alice\Model\Author' => [
                'author0' => [
                    'fullname' => 'Charles Lutwidge Dodgson',
                ],
            ],
        ];

        $actual = $this->merger->mergeInclude($data, $include);
        $this->assertSame($expected, $actual);
    }
}
