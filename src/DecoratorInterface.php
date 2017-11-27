<?php

namespace XTAIN\Process;

interface DecoratorInterface
{
    /**
     * @return array
     */
    public function command(array $command);

    /**
     * @param DecoratorInterface $strategy
     */
    public function decorate(DecoratorInterface $strategy);
}