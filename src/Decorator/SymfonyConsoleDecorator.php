<?php

namespace XTAIN\Process\Decorator;

use XTAIN\Process\CommandFinder;

class SymfonyConsoleDecorator extends AbstractDecorator
{
    /**
     * @var string
     */
    protected $kernelRootDir;

    /**
     * @var string
     */
    protected $environment;

    /**
     * SymfonyConsoleDecorator constructor.
     *
     * @param string $kernelRootDir
     * @param string $environment
     */
    public function __construct(
        $kernelRootDir,
        $environment = 'prod'
    ) {
        $this->kernelRootDir = $kernelRootDir;
        $this->environment = $environment;
    }

    /**
     * @param array $command
     * @return array
     * @throws \RuntimeException
     */
    public function command(array $command)
    {
        $finder = new CommandFinder($this->kernelRootDir);
        $console = $finder->find('console');
        if ($console === null) {
            throw new \RuntimeException('Could not find console');
        }

        $console = realpath($console);

        return array_merge(
            array(
                $console,
                '--env=' . $this->environment,
                '--no-debug'
            ),
            parent::command($command)
        );
    }

}