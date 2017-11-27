<?php

namespace XTAIN\Process;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use XTAIN\Process\Decorator\DefaultDecorator;

class Builder
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var DecoratorInterface
     */
    protected $decorator;

    /**
     * Builder constructor.
     *
     * @param string|array $command
     * @param DecoratorInterface $decorator
     */
    public function __construct(
        $command,
        DecoratorInterface $decorator = null
    ) {
        if ($decorator === null) {
            $decorator = new DefaultDecorator();
        }

        $this->decorator = $decorator;
        $this->command = $command;
    }

    /**
     * @param DecoratorInterface|DecoratorInterface[] $decorators,...
     *
     * @return DecoratorInterface
     */
    public static function chain($decorators)
    {
        /** @var DecoratorInterface[] $decorators */
        $decorators = Shell::unpack(func_get_args());

        $lastDecorator = array_shift($decorators);

        foreach ($decorators as $decorator) {
            $decorator->decorate($lastDecorator);
            $lastDecorator = $decorator;
        }

        return $lastDecorator;
    }

    /**
     * @return string[]
     */
    public function getCommand()
    {
        return $this->decorator->command((array) $this->command);
    }

    /**
     * @return ProcessBuilder
     */
    public function getProcessBuilder()
    {
        return new ProcessBuilder($this->getCommand());
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        return $this->getProcessBuilder()->getProcess();
    }

    /**
     * @return DaemonProcess
     */
    public function getDaemon()
    {
        return new DaemonProcess($this->getProcessBuilder());
    }
}