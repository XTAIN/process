<?php

namespace XTAIN\Process\Decorator;

use XTAIN\Process\Shell;

class ShellContextDecorator extends AbstractDecorator
{
    /**
     * @return array
     */
    public function command(array $command)
    {
        $shell = Shell::which(array('bash', 'sh'));

        return array(
            $shell,
            '-c',
            Shell::escape(parent::command($command))
        );
    }
}