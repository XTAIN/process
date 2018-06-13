<?php

namespace XTAIN\Process\Daemon;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class Data
{
    /**
     * @var Process
     */
    protected $process;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Data constructor.
     * @param Process         $process
     * @param LoggerInterface $logger
     */
    public function __construct(
        Process $process,
        LoggerInterface $logger = null
    ) {
        $this->process = $process;
        $this->logger = $logger;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }

    /**
     * @return null|string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return null|LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }
}