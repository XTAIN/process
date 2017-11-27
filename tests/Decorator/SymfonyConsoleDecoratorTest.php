<?php

namespace XTAIN\Tests\Process;

use Symfony\Component\Process\PhpExecutableFinder;
use XTAIN\Process\Builder;
use XTAIN\Process\BuilderFactory;
use XTAIN\Process\Decorator\PhpDecorator;
use XTAIN\Process\Decorator\SymfonyConsoleDecorator;

class SymfonyConsoleDecoratorTest extends AbstractBuilderTest
{
    const TEST_DIR = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'symfony';

    public function testBuilder()
    {
        $builderFactory = new BuilderFactory(array(
            new SymfonyConsoleDecorator(self::TEST_DIR),
            new PhpDecorator()
        ));

        $builder = $builderFactory->create('test:abc');

        $finder = new PhpExecutableFinder();

        $this->assertBuilder($builder);
        $this->assertEquals(array_merge(
            array($finder->find(false)),
            $finder->findArguments(),
            array(realpath(self::TEST_DIR . '/bin/console'), '--env=prod', '--no-debug', 'test:abc')
        ), $builder->getCommand());

        $process = $builder->getProcess();
        $process->run();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertEquals("--env=prod --no-debug test:abc", $process->getOutput());
    }
}