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

return [
    'Nelmio\Alice\DummyWithMethods' => [
        'dummy_with_methods' => [
            '__construct' => [
                '$foo1' => 'foo1',
                '$foo2' => 'foo2',
            ],
            '__calls' => [
                [
                    'bar' => [
                        '$bar1' => 'bar1',
                        '$bar2' => 'bar2',
                    ],
                ],
            ],
        ],
    ],
];
