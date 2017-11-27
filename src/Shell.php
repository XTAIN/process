<?php

namespace XTAIN\Process;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;

class Shell
{
    /**
     * @param array $arguments
     * @param array $elements
     *
     * @return array
     */
    public static function unpack(array $arguments, array &$elements = null)
    {
        if ($elements === null) {
            $elements = array();
        }

        foreach ($arguments as $argument) {
            if (is_array($argument)) {
                self::unpack($argument, $elements);
            } else {
                $elements[] = $argument;
            }
        }

        return $elements;
    }

    /**
     * @param string|array $commands,...
     * @return string|null
     */
    public static function which($commands)
    {
        $commands = self::unpack(func_get_args());
        $finder = new ExecutableFinder();

        foreach ($commands as $command) {
            $found = $finder->find($command);
            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    /**
     * @param string|array $commands,...
     * @return string
     */
    public static function escape($commands)
    {
        $commands = self::unpack(func_get_args());
        $command = '';

        foreach ($commands as $arg)
        {
            $command .= escapeshellarg($arg) . ' ';
        }

        return rtrim($command);
    }

    /**
     * @return string
     */
    public static function php()
    {
        $phpFinder = new PhpExecutableFinder();
        return $phpFinder->find();
    }

    /**
     * @param int $pid
     * @return bool
     */
    public static function isRunning($pid)
    {
        if (function_exists('posix_kill')) {
            return posix_kill($pid, 0);
        } else {
            return is_dir('/proc/' . $pid);
        }
    }
}