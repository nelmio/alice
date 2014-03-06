<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Util;

use Nelmio\Alice\ORMInterface;

class FlagParser {

	public static function parse($key)
	{
		$flags = array();
		if (preg_match('{^(.+?)\s*\((.+)\)$}', $key, $matches)) {
			foreach (preg_split('{\s*,\s*}', $matches[2]) as $flag) {
				$val = true;
				if ($pos = strpos($flag, ':')) {
					$flag = trim(substr($flag, 0, $pos));
					$val = trim(substr($flag, $pos+1));
				}
				$flags[$flag] = $val;
			}
			$key = $matches[1];
		}

		return array($key, $flags);
	}

}