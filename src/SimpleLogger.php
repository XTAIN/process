<?php

namespace XTAIN\Process;

use Psr\Log\AbstractLogger;

class SimpleLogger extends AbstractLogger
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var resource
     */
    protected $resource;

    public function __construct($file)
    {
        $this->file = $file;
    }

    public function getHandle()
    {
        if ($this->resource === null) {
            $this->resource = fopen($this->file, 'a');
        }

        return $this->resource;
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        fputs($this->getHandle(), $level.':' . $message . "\n");
    }

    public function __destruct()
    {
        fclose($this->getHandle());
        $this->resource = null;
    }
}