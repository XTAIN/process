<?php

namespace XTAIN\Process;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use XTAIN\Process\Decorator\DefaultDecorator;

class Builder implements LoggerAwareInterface
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
     * @var LoggerInterface
     */
    protected $logger;

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
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
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
     * @return string
     */
    public function getEscapedCommand()
    {
        $command = '';

        foreach ($this->getCommand() as $item) {
            $command .= Shell::escape($item) . ' ';
        }

        return trim($command);
    }

    /**
     * @return Process
     */
    public function getProcess()
    {
        $process = new Process($this->getEscapedCommand());
        $process->setTimeout(0);
        return $process;
    }

    /**
     * @return DaemonProcess
     */
    public function getDaemon()
    {
        $daemon = new DaemonProcess($this->getProcess());

        if ($this->logger !== null) {
            $daemon->setLogger($this->logger);
        }

        return $daemon;
    }
}