<?php

namespace XTAIN\Tests\Process;

use XTAIN\Process\Builder;
use XTAIN\Process\Decorator\ShellDecorator;

class ShellDecoratorTest extends AbstractBuilderTest
{
    public function testBuilder()
    {
        $builder = new Builder('sleep 1; echo 1', new ShellDecorator());

        $this->assertBuilder($builder);
        $this->assertEquals(array('/usr/bin/bash', '-c', "sleep 1; echo 1"), $builder->getCommand());

        $process = $builder->getProcess();
        $process->run();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertEquals("1\n", $process->getOutput());
    }
}