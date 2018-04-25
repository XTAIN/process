<?php

namespace XTAIN\Tests\Process;

use XTAIN\Process\Builder;
use XTAIN\Process\Decorator\ShellDecorator;
use XTAIN\Process\Shell;

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

    public function testEscapeSingleQoute()
    {
        $builder = new Builder("echo '1'", new ShellDecorator());

        $this->assertBuilder($builder);
        $this->assertEquals(array('/usr/bin/bash', '-c', "echo '1'"), $builder->getCommand());
        $this->assertEquals("'/usr/bin/bash' '-c' 'echo '\\''1'\\'''", $builder->getEscapedCommand());

        $process = $builder->getProcess();
        $process->run();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertEquals("1\n", $process->getOutput());
    }

    public function testEscapeDoubleQoute()
    {
        $builder = new Builder('echo "1"', new ShellDecorator());

        $this->assertBuilder($builder);
        $this->assertEquals(array('/usr/bin/bash', '-c', 'echo "1"'), $builder->getCommand());
        $this->assertEquals("'/usr/bin/bash' '-c' 'echo \"1\"'", $builder->getEscapedCommand());

        $process = $builder->getProcess();
        $process->run();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertEquals("1\n", $process->getOutput());
    }

    public function testEscapeBothQoute()
    {
        $builder = new Builder('echo "1"\'1\'', new ShellDecorator());

        $this->assertBuilder($builder);
        $this->assertEquals(array('/usr/bin/bash', '-c', 'echo "1"\'1\''), $builder->getCommand());
        $this->assertEquals("'/usr/bin/bash' '-c' 'echo \"1\"'\''1'\'''", $builder->getEscapedCommand());

        $process = $builder->getProcess();
        $process->run();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertEquals("11\n", $process->getOutput());
    }

    public function testDoubleEscape()
    {
        $builder = new Builder('echo '.Shell::escape("t\\e's\"t"), new ShellDecorator());

        $this->assertBuilder($builder);
        $this->assertEquals(array('/usr/bin/bash', '-c', "echo 't\\e'\''s\"t'"), $builder->getCommand());
        $this->assertEquals("'/usr/bin/bash' '-c' 'echo '\''t\\e'\''\'\'''\''s\"t'\'''", $builder->getEscapedCommand());

        $process = $builder->getProcess();
        $process->run();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertEquals("t\\e's\"t\n", $process->getOutput());
    }

    public function testUtf8()
    {
        $builder = new Builder('echo "😅⛄"\'😅⛄\'😅⛄', new ShellDecorator());

        $this->assertBuilder($builder);
        $this->assertEquals(array('/usr/bin/bash', '-c', 'echo "😅⛄"\'😅⛄\'😅⛄'), $builder->getCommand());
        $this->assertEquals("'/usr/bin/bash' '-c' 'echo \"😅⛄\"'\''😅⛄'\''😅⛄'", $builder->getEscapedCommand());

        $process = $builder->getProcess();
        $process->run();
        $process->wait();
        $this->assertEquals(0, $process->getExitCode());
        $this->assertEquals("😅⛄😅⛄😅⛄\n", $process->getOutput());
    }
}