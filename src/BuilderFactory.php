<?php

namespace XTAIN\Process;

class BuilderFactory
{
    /**
     * @var array
     */
    protected $decorators = array();

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
        return new Builder($command, Builder::chain($this->decorators));
    }
}