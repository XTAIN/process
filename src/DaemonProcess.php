<?php

namespace XTAIN\Process;

use Composer\Autoload\ClassLoader;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;
use XTAIN\Process\Daemon\Data;

class DaemonProcess implements LoggerAwareInterface
{
    const RUNNER = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'runner.php';

    const FORK = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'fork.php';

    /**
     * @var Process
     */
    protected $process;

    /**
     * @var bool
     */
    protected $usePcntl;

    /**
     * @var string
     */
    protected $tempDirectory;

    /**
     * @var int
     */
    protected $childPid;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var LoggerInterface
     */
    protected $childLogger;

    /**
     * NohupProcess constructor.
     *
     * @param Process $process
     * @param boolean $usePcntl
     * @param string  $tempDirectory
     */
    public function __construct(Process $process, $usePcntl = true, $tempDirectory = null)
    {
        $this->process = $process;
        $this->usePcntl = $usePcntl;
        $this->tempDirectory = $tempDirectory;
        if ($tempDirectory === null) {
            $this->tempDirectory = sys_get_temp_dir();
        }
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setChildLogger(LoggerInterface $logger)
    {
        $this->childLogger = $logger;
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
        if ($this->logger !== null) {
            $this->logger->debug('Start process using fork with command {cmd}', array(
                'cmd' => $cmd
            ));
        }

        $input = new InputStream();
        $bgProcess = new Process($cmd);
        $bgProcess->setInput($input);
        $bgProcess->start();
        $input->write($this->getPayload($data));
        $input->close();
        $bgProcess->wait();
        $pid = trim($bgProcess->getOutput());

        if (empty($pid)) {
            if ($this->logger !== null) {
                $this->logger->warning('Starting process failed');
            }

            return -1;
        }

        if ($this->logger !== null) {
            $this->logger->debug('Started process with pid {pid}', array(
                'pid' => $pid
            ));
        }

        return $pid;
    }

    /**
     * @return bool
     */
    protected function supportsFork()
    {
        $check = new Process(Shell::escape(Shell::php(), self::FORK));
        return $check->run() === 0;
    }

    /**
     * @param string $cmd
     * @param Data   $data
     *
     * @return int
     */
    protected function emulateFork($cmd, Data $data)
    {
        if ($this->logger !== null) {
            $this->logger->debug('Start process using emulate fork with command {cmd}', array(
                'cmd' => $cmd
            ));
        }

        $stdin = tempnam($this->tempDirectory, 'dae_proc_stdin');
        $stdout = tempnam($this->tempDirectory, 'dae_proc_stdout');

        $nohup = Shell::which('nohup');
        if ($nohup === null) {
            $nohup = '';
        }

        file_put_contents($stdin, $this->getPayload($data));

        if (!file_exists($stdin)) {
            throw new ProcessException('could not create stdin file');
        }

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
            if ($this->logger !== null) {
                $this->logger->debug('Started process with pid {pid}', array(
                    'pid' => $pid
                ));
            }

            return $pid;
        }

        if ($this->logger !== null) {
            $this->logger->warning('Starting process failed');
        }

        return -1;
    }

    public function run()
    {
        $dto = new Daemon\Data($this->process, $this->childLogger);
        $fork = $this->usePcntl && self::supportsFork();

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