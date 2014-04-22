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

use Symfony\Component\Yaml\Yaml as YamlParser;
use UnexpectedValueException;

use Nelmio\Alice\Fixtures\Parser\Methods\Base;

class Yaml extends Base
{
  /**
   * {@inheritDoc}
   **/
  protected $extension = 'ya?ml';

  /**
   * {@inheritDoc}
   */
  public function parse($file)
  {
    $yaml = $this->compilePhp($file);
    $data = YamlParser::parse($yaml);

    if (!is_array($data)) {
      throw new UnexpectedValueException('Yaml files must parse to an array of data');
    }

    $data = $this->processIncludes($data, $file);

    return $data;
  }

  /**
   * @param array $data
   * @param string $filename
   * @return mixed
   */
  private function processIncludes($data, $filename)
  {
    if (isset($data['include'])) {
      foreach ($data['include'] as $include) {
        $includeFile = dirname($filename) . DIRECTORY_SEPARATOR . $include;
        $includeData = $this->parseFile($includeFile);
        $data = $this->mergeIncludeData($data, $includeData);
      }
    }

    unset($data['include']);

    return $data;
  }

  /**
   * @param array $data
   * @param array $includeData
   */
  private function mergeIncludeData($data, $includeData)
  {
    foreach ($includeData as $class => $fixtures) {
      if (isset($data[$class])) {
        $data[$class] = array_merge($fixtures, $data[$class]);
      } else {
        $data[$class] = $fixtures;
      }
    }

    return $data;
  }

}
