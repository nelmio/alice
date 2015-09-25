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
  'include' => [
    'includes/product.php',
    'includes/file1.php',
  ],
  'Nelmio\Alice\fixtures\Product' => [
    'product0' => [
      'changed' => 'y',
      'user' => '@user1',
    ],
    'product1' => [
      'amount' => 45,
      'paid' => 'n',
      'user' => '@user0',
      ],
  ],
  'Nelmio\Alice\fixtures\Shop' => [
    'shop1' => [
      'domain' => 'ebay.com',
    ],
  ],
];
