<?php
return array(
  'include' => array(
    'includes/product.php',
    'includes/file1.php'
  ),
  'Nelmio\Alice\fixtures\Product' => array(
    'product0' => array(
      'changed' => 'y',
      'user' => '@user1'
    ),
    'product1' => array(
      'amount' => 45,
      'paid' => 'n',
      'user' => '@user0',
      )
  ),
  'Nelmio\Alice\fixtures\Shop' => array(
    'shop1' => array(
      'domain' => 'ebay.com'
    )
  )
);