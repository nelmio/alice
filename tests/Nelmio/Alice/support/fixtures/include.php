<?php
return [
  'include' => [
    'includes/product.php',
    'includes/file1.php'
  ],
  'Nelmio\Alice\fixtures\Product' => [
    'product0' => [
      'changed' => 'y',
      'user' => '@user1'
    ],
    'product1' => [
      'amount' => 45,
      'paid' => 'n',
      'user' => '@user0',
      ]
  ],
  'Nelmio\Alice\fixtures\Shop' => [
    'shop1' => [
      'domain' => 'ebay.com'
    ]
  ]
];
