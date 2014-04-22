<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Parser\Methods;

use UnexpectedValueException;

use Nelmio\Alice\Fixtures\Parser\Methods\MethodInterface;

class Php implements MethodInterface
{
  /**
   * The context allows any kind of contextual information to be available in fixtures
   *
   * @var mixed
   **/
  private $context;

  /**
   * @var string
   **/
  protected $extension = 'php';

  public function __construct($context = null)
  {
    $this->context = $context;
  }

  /**
   * {@inheritDoc}
   */
  public function canParse($file)
  {
    // we add (\.php)? to the regex to allow extensions of this parser to first
    // be compiled by php
    return preg_match("/\.{$this->extension}(\.php)?$/", $file);
  }

  /**
   * {@inheritDoc}
   */
  public function parse($file)
  {
    $context = $this->context;
    $includeWrapper = function () use ($file, $context) {
      ob_start();
      $res = include $file;
      ob_end_clean();

      return $res;
    };

    $data = $includeWrapper();
    if (!is_array($data)) {
      throw new UnexpectedValueException("Included file \"{$file}\" must return an array of data");
    }

    return $data;
  }

}
