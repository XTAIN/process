<?php

namespace XTAIN\Process\Decorator;

use XTAIN\Process\DecoratorInterface;

abstract class AbstractDecorator implements DecoratorInterface
{
    /**
     * @var DecoratorInterface
     */
    protected $strategy;

    /**
     * @return array
     */
    public function command(array $command)
    {
        if ($this->strategy !== null) {
            $command = $this->strategy->command($command);
        }

        return $command;
    }

    /**
     * @param DecoratorInterface $strategy
     */
    public function decorate(DecoratorInterface $strategy)
    {
        $this->strategy = $strategy;
    }
}