<?php
return [
    'include' => [
        'includes/foo.php'
    ],
    'Bar' => [
        'bar' => [
            'id' => 1,
            'text' => '<@foo->text>'
        ],
    ],
];
