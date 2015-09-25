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
  'Nelmio\Alice\fixtures\Product' => [
    'product_base (template)' => [
      'status' => 'in_stock',
      'site' => '<word()>',
      'changed' => 'n',
      'locked' => '<word()>',
      'cancelled' => '<word()>',
      'canBuy' => 'y',
      'package' => 'n',
      'price' => '<randomFloat()>',
      'amount' => '1',
      'markDeleted' => '<word()>',
      'paid' => 'y',
    ],
  ],
];
