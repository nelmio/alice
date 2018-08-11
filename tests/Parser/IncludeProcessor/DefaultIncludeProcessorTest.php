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

namespace Nelmio\Alice\Parser\IncludeProcessor;

use Nelmio\Alice\FileLocator\DefaultFileLocator;
use Nelmio\Alice\FileLocatorInterface;
use Nelmio\Alice\Parser\IncludeProcessorInterface;
use Nelmio\Alice\ParserInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use ReflectionClass;

/**
 * @covers \Nelmio\Alice\Parser\IncludeProcessor\DefaultIncludeProcessor
 */
class DefaultIncludeProcessorTest extends TestCase
{
    private static $dir;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        self::$dir = __DIR__.'/../../../fixtures/Parser/files/cache';
    }

    public function testIsAnIncludeProcessor()
    {
        $this->assertTrue(is_a(DefaultIncludeProcessor::class, IncludeProcessorInterface::class, true));
    }

    public function testIsNotClonable()
    {
        $this->assertFalse((new ReflectionClass(DefaultIncludeProcessor::class))->isCloneable());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Could not find any include statement in the file "dummy.php".
     */
    public function testThrowsAnExceptionIfNoIncludeStatementFound()
    {
        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /* @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $fileLocatorProphecy = $this->prophesize(FileLocatorInterface::class);
        $fileLocatorProphecy->locate('dummy.php')->willReturn('dummy.php');
        /* @var FileLocatorInterface $fileLocator */
        $fileLocator = $fileLocatorProphecy->reveal();

        $processor = new DefaultIncludeProcessor($fileLocator);

        $processor->process($parser, 'dummy.php', []);
    }

    public function testIncludeStatementCanBeNull()
    {
        $mainFile = self::$dir.'/main.yml';   // needs to be a real file to be cached
        $parsedMainFileContent = [
            'include' => null,
            'Nelmio\Alice\Model\User' => [
                'user_main' => [],
            ],
        ];
        $expected = [
            'Nelmio\Alice\Model\User' => [
                'user_main' => [],
            ],
        ];

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /* @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $fileLocatorProphecy = $this->prophesize(FileLocatorInterface::class);
        $fileLocatorProphecy->locate($mainFile)->willReturn('main.yml');
        /* @var FileLocatorInterface $fileLocator */
        $fileLocator = $fileLocatorProphecy->reveal();

        $processor = new DefaultIncludeProcessor($fileLocator);

        $actual = $processor->process($parser, $mainFile, $parsedMainFileContent);

        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessageRegExp /^Expected include statement to be either null or an array of files to include\. Got "string" instead in file ".+\/main\.yml"\.$/
     */
    public function testIfNotNullIncludeStatementMustBeAnArray()
    {
        $mainFile = self::$dir.'/main.yml';   // needs to be a real file to be cached
        $parsedMainFileContent = [
            'include' => 'stringValue',
            'Nelmio\Alice\Model\User' => [
                'user_main' => [],
            ],
        ];

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /* @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $processor = new DefaultIncludeProcessor(new DefaultFileLocator());

        $processor->process($parser, $mainFile, $parsedMainFileContent);
    }

    /**
     * @expectedException \TypeError
     * @expectedExceptionMessageRegExp /^Expected elements of include statement to be file names\. Got "boolean" instead in file ".+\/main\.yml"\.$/
     */
    public function testIncludedFilesMustBeStrings()
    {
        $mainFile = self::$dir.'/main.yml';   // needs to be a real file to be cached
        $parsedMainFileContent = [
            'include' => [
                false,
            ],
            'Nelmio\Alice\Model\User' => [
                'user_main' => [],
            ],
        ];

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /* @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $processor = new DefaultIncludeProcessor(new DefaultFileLocator());

        $processor->process($parser, $mainFile, $parsedMainFileContent);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /^Expected elements of include statement to be file names\. Got empty string instead in file ".+\/main\.yml"\.$/
     */
    public function testIncludedFilesMustBeNonEmptyStrings()
    {
        $mainFile = self::$dir.'/main.yml';   // needs to be a real file to be cached
        $parsedMainFileContent = [
            'include' => [
                '',
            ],
            'Nelmio\Alice\Model\User' => [
                'user_main' => [],
            ],
        ];

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /* @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $processor = new DefaultIncludeProcessor(new DefaultFileLocator());

        $processor->process($parser, $mainFile, $parsedMainFileContent);
    }

    public function testProcessesIncludeFiles()
    {
        $mainFile = self::$dir.'/main.yml';   // needs to be a real file to be cached
        $parsedMainFileContent = [
            'include' => [
                $file1Path = 'file1.yml',
                $file2Path = 'another_level/file2.yml',
            ],
            'Nelmio\Alice\Model\User' => [
                'user_main' => [],
            ],
        ];
        $parsedFile1Content = [
            'Nelmio\Alice\Model\User' => [
                'user_file1' => [],
            ],
        ];
        $parsedFile2Content = [
            'include' => [
                $file3Path = self::$dir.'/file3.yml',
            ],
            'Nelmio\Alice\Model\User' => [
                'user_file2' => [],
            ],
        ];
        $parsedFile3Content = [
            'Nelmio\Alice\Model\User' => [
                'user_file3' => [],
            ],
        ];
        $expected = [
            'Nelmio\Alice\Model\User' => [
                'user_file1' => [],
                'user_file3' => [],
                'user_file2' => [],
                'user_main' => [],
            ],
        ];

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse($mainFile)->willReturn($parsedMainFileContent);
        $parserProphecy->parse(Argument::containingString('file1.yml'))->willReturn($parsedFile1Content);
        $parserProphecy->parse(Argument::containingString('file2.yml'))->willReturn($parsedFile2Content);
        $parserProphecy->parse(Argument::containingString('file3.yml'))->willReturn($parsedFile3Content);
        /* @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $fileLocatorProphecy = $this->prophesize(FileLocatorInterface::class);
        $fileLocatorProphecy->locate('main.yml', Argument::cetera())->willReturn('main.yml');
        $fileLocatorProphecy->locate($mainFile, Argument::cetera())->willReturn('main.yml');
        $fileLocatorProphecy->locate($file1Path, Argument::cetera())->willReturn('file1.yml');
        $fileLocatorProphecy->locate($file2Path, Argument::cetera())->willReturn('file2.yml');
        $fileLocatorProphecy->locate($file3Path, Argument::cetera())->willReturn('file3.yml');
        /* @var FileLocatorInterface $fileLocator */
        $fileLocator = $fileLocatorProphecy->reveal();

        $processor = new DefaultIncludeProcessor($fileLocator);

        $actual = $processor->process($parser, $mainFile, $parsedMainFileContent);

        $this->assertSame($expected, $actual);
    }
}
