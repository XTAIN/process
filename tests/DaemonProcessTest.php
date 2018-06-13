<?php

namespace XTAIN\Tests\Process;

use Symfony\Component\Process\Process;
use XTAIN\Process\Builder;
use XTAIN\Process\Decorator\ShellDecorator;
use XTAIN\Process\DaemonProcess;
use XTAIN\Process\Shell;
use XTAIN\Process\SimpleLogger;

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

    /**
     * @param bool $useFork
     *
     * @dataProvider forkProvider
     */
    public function testRun($useFork = false)
    {
        $builder = new Builder('sleep 5; echo 1', new ShellDecorator());
        $nohup = new DaemonProcess($builder->getProcess(), $useFork);
        $this->doTest($nohup);
    }

    /**
     * @param bool $useFork
     *
     * @dataProvider forkProvider
     */
    public function testRunWithLog($useFork = false)
    {
        $tmplog = tempnam(sys_get_temp_dir(), 'proctest');
        $logger = new SimpleLogger($tmplog);

        $builder = new Builder('sleep 5; echo 1234; sleep 5; echo fofo', new ShellDecorator());
        $nohup = new DaemonProcess($builder->getProcess(), $useFork);
        $nohup->setChildLogger($logger);
        $nohup->run();
        $nohup->wait();
        sleep(1);

        $this->assertEquals("info:1234\n\ninfo:fofo\n\n", file_get_contents($tmplog));
    }

    /**
     * @return bool[]
     */
    public static function forkProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }

}
