<?php

return array(
    'Nelmio\Alice\support\models\User' => array(
        'user1' => array(
            'username' => '<identity($fake("upperCaseProvider", null, "John Doe"))>',
            'fullname' => '<upperCaseProvider("John Doe")>',
        ),
        'user2' => array(
            'username' => $fake('identity', null, $fake('upperCaseProvider', null, 'John Doe')),
            'fullname' => $fake('upperCaseProvider', null, 'John Doe'),
        ),
    ),
);
