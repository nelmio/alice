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
        'product_template.php',
    ],
    'Nelmio\Alice\Entity\Product' => [
        'product1 (extends product_base)' => [
            'amount' => 45,
        ],
    ],
    'Nelmio\Alice\Entity\Shop' => [
        'shop' => [
            'status' => 'none',
        ],
    ],
];
