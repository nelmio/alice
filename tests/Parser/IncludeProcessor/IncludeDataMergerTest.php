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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\Parser\IncludeProcessor\IncludeDataMerger
 */
class IncludeDataMergerTest extends TestCase
{
    /**
     * @var IncludeDataMerger
     */
    private $merger;


    protected function setUp(): void
    {
        $this->merger = new IncludeDataMerger();
    }

    /**
     * @return \Generator
     */
    public function provideDataForTestMergesNonArrayData(): \Generator
    {
        $data = [
            'parameters' => 'foo1',
        ];
        $include = [
            'parameters' => 'bar1',
        ];
        $expected = [
            'parameters' => 'foo1',
        ];

        yield [
            $data,
            $include,
            $expected
        ];

        $data = [
            'parameters' => [
                'foo2',
            ],
        ];
        $include = [
            'parameters' => 'bar2',
        ];
        $expected = [
            'parameters' => [
                'foo2',
            ],
        ];

        yield [
            $data,
            $include,
            $expected
        ];

        $data = [
            'parameters' => 'foo3',
        ];
        $include = [
            'parameters' => ['bar3'],
        ];
        $expected = [
            'parameters' => 'foo3',
        ];

        yield [
            $data,
            $include,
            $expected
        ];
    }

    /**
     * @dataProvider provideDataForTestMergesNonArrayData
     *
     * @param array $data
     * @param array $include
     * @param array $expected
     */
    public function testMergesNonArrayData(array $data, array $include, array $expected): void
    {
        $actual = $this->merger->mergeInclude($data, $include);
        static::assertSame($expected, $actual);
    }

    public function testMergesParametersAndReturnTheResult(): void
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
                'bar' => 'rab',
                'white' => 'rabbit',
            ],
        ];

        $actual = $this->merger->mergeInclude($data, $include);
        static::assertSame($expected, $actual);
    }

    public function testCanMergeClassNames(): void
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
                'user1' => [
                    'fullname' => 'Alice',
                ],
                'user0' => [
                    'fullname' => 'Bob',
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
        static::assertSame($expected, $actual);
    }

    public function testCanMergeCompleteDataSet(): void
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
                'bar' => 'rab',
                'white' => 'rabbit',
            ],
            'Nelmio\Alice\Model\User' => [
                'user1' => [
                    'fullname' => 'Alice',
                ],
                'user0' => [
                    'fullname' => 'Bob',
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
        static::assertSame($expected, $actual);
    }
}
