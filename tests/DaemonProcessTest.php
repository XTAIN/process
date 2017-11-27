<?php

namespace XTAIN\Tests\Process;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use XTAIN\Process\Builder;
use XTAIN\Process\Decorator\ShellDecorator;
use XTAIN\Process\DaemonProcess;
use XTAIN\Process\Shell;

class DaemonProcessTest extends \PHPUnit\Framework\TestCase
{
    protected function doTest(DaemonProcess $nohup)
    {
        $time = microtime(true);
        $this->assertNull($nohup->getPid());
        $nohup->run();
        $this->assertInternalType('int', $nohup->getPid());
        sleep(2);
        $this->assertLessThanOrEqual(3, microtime(true) - $time);
        $this->assertTrue($nohup->isRunning());
        $nohup->wait();
        $this->assertGreaterThanOrEqual(5, microtime(true) - $time);
        $this->assertFalse($nohup->isRunning());
    }

    public function testRun()
    {
        $builder = new Builder('sleep 5; echo 1', new ShellDecorator());
        $nohup = new DaemonProcess($builder->getProcessBuilder());
        $this->doTest($nohup);
    }

    public function testRunEmulated()
    {
        $builder = new Builder('sleep 5; echo 1', new ShellDecorator());
        $nohup = new DaemonProcess($builder->getProcessBuilder(), false);
        $this->doTest($nohup);
    }
}