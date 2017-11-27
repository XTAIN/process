<?php

namespace XTAIN\Process\Decorator;

class DefaultDecorator extends AbstractDecorator
{
    /**
     * @return array
     */
    public function command(array $command)
    {
        return parent::command($command);
    }
}