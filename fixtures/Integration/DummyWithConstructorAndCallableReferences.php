<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

return [
    \Nelmio\Alice\Entity\DummyWithConstructorAndCallable::class => [
        'dummy_template (template)' => [
            '__calls' => [
                [
                    'reset' => []
                ]
            ]
        ],
        'dummy (extends dummy_template)' => [
            '__construct' => ['foo']
        ]
    ],
    \Nelmio\Alice\Entity\DummyWithConstructorParam::class => [
        'foo-0' => [
            '__construct' => ['@dummy->getFoo']
        ],
    ],
];
