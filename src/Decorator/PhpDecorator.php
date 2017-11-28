<?php

namespace XTAIN\Process\Decorator;

use Symfony\Component\Process\PhpExecutableFinder;
use XTAIN\Process\Shell;

class PhpDecorator extends AbstractDecorator
{
    /**
     * @var array
     */
    protected $config;

    /**
     * PhpDecorator constructor.
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function command(array $command)
    {
        $phpFinder = new PhpExecutableFinder();

        $args = array();

        foreach ($this->config as $key => $value) {
            $args[] = '-d';
            $args[] = $key . '=' . $value;
        }

        return array_merge(
            array(Shell::php(false)),
            $phpFinder->findArguments(),
            $args,
            parent::command($command)
        );
    }
}