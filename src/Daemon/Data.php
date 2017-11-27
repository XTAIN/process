<?php

namespace XTAIN\Process\Daemon;

use Symfony\Component\Process\Process;

class Data
{
    /**
     * @var Process
     */
    protected $process;

    /**
     * Data constructor.
     * @param Process $process
     */
    public function __construct(
        Process $process
    ) {
        $this->process = $process;
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->process;
    }
}