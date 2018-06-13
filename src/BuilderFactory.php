<?php

namespace XTAIN\Process;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class BuilderFactory implements LoggerAwareInterface
{
    /**
     * @var array
     */
    protected $decorators = array();

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * BuilderFactory constructor.
     *
     * @param array $decorators
     */
    public function __construct(array $decorators)
    {
        $this->decorators = $decorators;
    }

    /**
     * @param string|array $command
     *
     * @return Builder
     */
    public function create($command)
    {
        $builder = new Builder($command, Builder::chain($this->decorators));

        if ($this->logger !== null) {
            $builder->setLogger($this->logger);
        }

        return $builder;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}