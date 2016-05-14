<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

return [
    'include' => [
        'included.php',
    ],
    // Case where parameters block is after include block
    'parameters' => [
        'foo' => 'bar',
    ],
];
