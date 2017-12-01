<?php

namespace XTAIN\Process;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use XTAIN\Process\Daemon\Data;

class DaemonProcess
{
    const RUNNER = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'runner.php';

    /**
     * @var ProcessBuilder
     */
    protected $builder;

    /**
     * @var bool
     */
    protected $usePcntl;

    /**
     * @var int
     */
    protected $childPid;

    /**
     * NohupProcess constructor.
     *
     * @param ProcessBuilder $builder
     * @param boolean        $usePcntl
     */
    public function __construct(ProcessBuilder $builder, $usePcntl = true)
    {
        $this->builder = $builder;
        $this->usePcntl = $usePcntl;
    }

    /**
     * @return string
     */
    protected function getAutoloadFile()
    {
        $reflector = new \ReflectionClass(ClassLoader::class);
        return realpath(dirname($reflector->getFileName()) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'autoload.php');
    }

    /**
     * @param Data $data
     * @return string
     */
    protected function getPayload(Data $data)
    {
        return $this->getAutoloadFile() . "\n" . serialize($data);
    }

    /**
     * @param $cmd
     * @param Data $data

     * @return int
     */
    protected function fork($cmd, Data $data)
    {
        $input = new InputStream();
        $bgProcess = new Process($cmd);
        $bgProcess->setInput($input);
        $bgProcess->start();
        $input->write($this->getPayload($data));
        $input->close();
        $bgProcess->wait();
        $pid = trim($bgProcess->getOutput());

        if (empty($pid)) {
            return -1;
        }

        return $pid;
    }

    /**
     * @param string $cmd
     * @param Data   $data
     *
     * @return int
     */
    protected function emulateFork($cmd, Data $data)
    {
        $stdin = tempnam(sys_get_temp_dir(), 'dae_proc_stdout');
        $stdout = tempnam(sys_get_temp_dir(), 'dae_proc_stdout');

        $nohup = Shell::which('nohup');
        if ($nohup === null) {
            $nohup = '';
        }

        file_put_contents($stdin, $this->getPayload($data));

        $cmd = trim($nohup . ' ' . $cmd . ' ' . Shell::escape(
            $stdin,
            $stdout
        ) . ' > /dev/null 2>&1 &');

        proc_close(proc_open($cmd, array(), $pipes));

        $pid = null;

        while (true) {
            usleep(1000000 / 10);

            $fo = fopen($stdout, 'r');
            $ex = 1;
            if (flock($fo, LOCK_SH, $ex)) {
                $pid = stream_get_contents($fo);
                flock($fo, LOCK_UN);

                if (strlen($pid) > 0) {
                    break;
                }
            }
            fclose($fo);
        }

        unlink($stdin);
        unlink($stdout);

        if ($pid !== null) {
            return $pid;
        }

        return -1;
    }

    public function run()
    {
        $process = $this->builder->getProcess();
        $dto = new Daemon\Data($process);
        $fork = $this->usePcntl && function_exists('pcntl_fork') && function_exists('posix_setsid');

        $cmd = Shell::escape(Shell::php(), self::RUNNER);

        if ($fork) {
            $childPid = $this->fork($cmd, $dto);
        } else {
            $childPid = $this->emulateFork($cmd, $dto);
        }

        $this->childPid = $childPid;
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return $this->childPid !== null && Shell::isRunning($this->childPid);
    }

    /**
     * @return int
     */
    public function getPid()
    {
        if ($this->childPid === null) {
            return null;
        }

        return (int) $this->childPid;
    }

    public function wait()
    {
        while ($this->isRunning()) {
            usleep(1000);
        }
    }
}