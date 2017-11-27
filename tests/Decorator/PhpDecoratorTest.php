<?php

namespace XTAIN\Tests\Process;

use Symfony\Component\Process\PhpExecutableFinder;
use XTAIN\Process\Builder;
use XTAIN\Process\Decorator\PhpDecorator;
use XTAIN\Process\Decorator\ShellContextDecorator;
use XTAIN\Process\Shell;

class PhpDecoratorTest  extends AbstractBuilderTest
{
    const TEST_FILE = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'test.php';

    public function testBuilder()
    {
        $builder = new Builder(
            self::TEST_FILE,
            new PhpDecorator()
        );

        $finder = new PhpExecutableFinder();

        $this->assertBuilder($builder);
        $this->assertEquals(array_merge(
            array($finder->find(false)),
            $finder->findArguments(),
            array(self::TEST_FILE)
        ), $builder->getCommand());

        $process = $builder->getProcess();
        $process->run();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertEquals("test123", $process->getOutput());
    }

    public function testConfigBuilder()
    {
        $builder = new Builder(
            self::TEST_FILE,
            new PhpDecorator(array(
                'memory_limit' => '32M'
            ))
        );

        $finder = new PhpExecutableFinder();

        $this->assertBuilder($builder);
        $this->assertEquals(array_merge(
            array($finder->find(false)),
            $finder->findArguments(),
            array('-d', 'memory_limit=32M'),
            array(self::TEST_FILE)
        ), $builder->getCommand());

        $process = $builder->getProcess();
        $process->run();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertEquals("test123", $process->getOutput());
    }

    public function testShellBuilder()
    {
        $builder = new Builder(
            self::TEST_FILE,
            Builder::chain(
                new PhpDecorator(),
                new ShellContextDecorator()
            )
        );

        $finder = new PhpExecutableFinder();

        $this->assertBuilder($builder);
        $this->assertEquals(array(
            '/usr/bin/bash',
            '-c',
            Shell::escape($finder->find(false), $finder->findArguments(), self::TEST_FILE)
        ), $builder->getCommand());

        $process = $builder->getProcess();
        $process->run();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertEquals("test123", $process->getOutput());
    }
}