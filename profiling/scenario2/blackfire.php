<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice;

use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

require_once __DIR__.'/../../vendor-bin/profiling/vendor/autoload.php';

$loader = new NativeLoader();
$blackfire = new \Blackfire\Client();

$config = new \Blackfire\Profile\Configuration();
$config->setTitle('Scenario 2');
$config->setSamples(10);
$config->setReference(2);

$probe = $blackfire->createProbe($config, false);

$probe->enable();
$loader->loadFile(__DIR__.'/fixtures.yml');
$probe->disable();

$blackfire->endProbe($probe);

$output = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
$output->success('Finished!');
