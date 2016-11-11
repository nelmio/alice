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
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

require_once __DIR__.'/../../vendor-bin/profiling/vendor/autoload.php';

$fileFinder = new Finder();
$fileFinder->files()->in(__DIR__)->depth(0)->name('*.yml');

$loader = new NativeLoader();
$blackfire = new \Blackfire\Client();

$config = new \Blackfire\Profile\Configuration();
$config->setTitle('Scenario 1');
$config->setSamples(10);
$config->setReference(5);

$probe = $blackfire->createProbe($config);
foreach ($fileFinder as $index => $file) {
    /** @var SplFileInfo $file */
    $loader->loadFile($file->getRealPath());
}
$blackfire->endProbe($probe);

$output = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
$output->success('Finished!');
