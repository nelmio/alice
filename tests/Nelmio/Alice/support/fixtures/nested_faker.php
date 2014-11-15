<?php

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
