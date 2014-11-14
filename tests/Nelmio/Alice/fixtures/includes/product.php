<?php

return array(
  'Nelmio\Alice\fixtures\Product' => array(
    'product_base (template)' => array(
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
    )
  )
);