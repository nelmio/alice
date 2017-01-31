<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor\Methods;

use Nelmio\Alice\Instances\Processor\Processable;

/**
 * @covers Nelmio\Alice\Instances\Processor\Methods\Reference
 */
class ReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Reference
     */
    private $method;

    public function setUp()
    {
        $this->method = new Reference();
    }

    /**
     * @dataProvider provideValues
     */
    public function testCanProcess($value, $parts)
    {
        $processable = new Processable($value);
        $this->method->canProcess($processable);

        foreach ($parts as $part => $partValue) {
            $this->assertEquals($partValue, $processable->getMatch($part));
        }
    }

    public function provideValues()
    {
        return [
            // nominal
            [
                '@user',
                [
                    'multi' => '',
                    'reference' => 'user',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user*',
                [
                    'multi' => '',
                    'reference' => 'user*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user->username',
                [
                    'multi' => '',
                    'reference' => 'user',
                    'sequence' => '',
                    'property' => 'username',
                ]
            ],
            [
                '2x @user*',
                [
                    'multi' => '2',
                    'reference' => 'user*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user{1..2}',
                [
                    'multi' => '',
                    'reference' => 'user',
                    'sequence' => '{1..2}',
                    'property' => '',
                ]
            ],

            // escaped quotes
            [
                '"@user"',
                [
                    'multi' => '',
                    'reference' => 'user',
                    'sequence' => '',
                    'property' => '',
                ]
            ],

            // digit
            [
                '@user1',
                [
                    'multi' => '',
                    'reference' => 'user1',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user1*',
                [
                    'multi' => '',
                    'reference' => 'user1*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user1->username',
                [
                    'multi' => '',
                    'reference' => 'user1',
                    'sequence' => '',
                    'property' => 'username',
                ]
            ],
            [
                '2x @user1*',
                [
                    'multi' => '2',
                    'reference' => 'user1*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user1{1..2}',
                [
                    'multi' => '',
                    'reference' => 'user1',
                    'sequence' => '{1..2}',
                    'property' => '',
                ]
            ],

            // period
            [
                '@user.',
                [
                    'multi' => '',
                    'reference' => 'user.',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user.*',
                [
                    'multi' => '',
                    'reference' => 'user.*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user.->username',
                [
                    'multi' => '',
                    'reference' => 'user.',
                    'sequence' => '',
                    'property' => 'username',
                ]
            ],
            [
                '2x @user.*',
                [
                    'multi' => '2',
                    'reference' => 'user.*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user.{1..2}',
                [
                    'multi' => '',
                    'reference' => 'user.',
                    'sequence' => '{1..2}',
                    'property' => '',
                ]
            ],

            // hyphen
            [
                '@user-',
                [
                    'multi' => '',
                    'reference' => 'user-',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user-*',
                [
                    'multi' => '',
                    'reference' => 'user-*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user-->username',
                [
                    'multi' => '',
                    'reference' => 'user-',
                    'sequence' => '',
                    'property' => 'username',
                ]
            ],
            [
                '2x @user-*',
                [
                    'multi' => '2',
                    'reference' => 'user-*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user-{1..2}',
                [
                    'multi' => '',
                    'reference' => 'user-',
                    'sequence' => '{1..2}',
                    'property' => '',
                ]
            ],

            // underscore
            [
                '@user_',
                [
                    'multi' => '',
                    'reference' => 'user_',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user_*',
                [
                    'multi' => '',
                    'reference' => 'user_*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user_->username',
                [
                    'multi' => '',
                    'reference' => 'user_',
                    'sequence' => '',
                    'property' => 'username',
                ]
            ],
            [
                '2x @user_*',
                [
                    'multi' => '2',
                    'reference' => 'user_*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user_{1..2}',
                [
                    'multi' => '',
                    'reference' => 'user_',
                    'sequence' => '{1..2}',
                    'property' => '',
                ]
            ],

            // slash
            [
                '@user/',
                [
                    'multi' => '',
                    'reference' => 'user/',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user/*',
                [
                    'multi' => '',
                    'reference' => 'user/*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user/->username',
                [
                    'multi' => '',
                    'reference' => 'user/',
                    'sequence' => '',
                    'property' => 'username',
                ]
            ],
            [
                '2x @user/*',
                [
                    'multi' => '2',
                    'reference' => 'user/*',
                    'sequence' => '',
                    'property' => '',
                ]
            ],
            [
                '@user/{1..2}',
                [
                    'multi' => '',
                    'reference' => 'user/',
                    'sequence' => '{1..2}',
                    'property' => '',
                ]
            ],
        ];
    }
}
