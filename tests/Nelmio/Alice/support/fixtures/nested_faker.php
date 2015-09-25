<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return [
    'Nelmio\Alice\support\models\User' => [
        'user1' => [
            'username' => '<identity($fake("upperCaseProvider", null, "John Doe"))>',
            'fullname' => '<upperCaseProvider("John Doe")>',
        ],
        'user2' => [
            'username' => $fake('identity', null, $fake('upperCaseProvider', null, 'John Doe')),
            'fullname' => $fake('upperCaseProvider', null, 'John Doe'),
        ],
    ],
];
