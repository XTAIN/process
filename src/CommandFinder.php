<?php

namespace XTAIN\Process;

class CommandFinder
{
    private $suffixes = array('.exe', '.bat', '.cmd', '.com');

    /**
     * @var string
     */
    private $root;

    /**
     * CommandFinder constructor.
     */
    public function __construct($root)
    {
        $this->root = $root;
    }

    /**
     * Replaces default suffixes of executable.
     *
     * @param array $suffixes
     */
    public function setSuffixes(array $suffixes)
    {
        $this->suffixes = $suffixes;
    }

    /**
     * Adds new possible suffix to check for executable.
     *
     * @param string $suffix
     */
    public function addSuffix($suffix)
    {
        $this->suffixes[] = $suffix;
    }

    /**
     * Finds an executable by name.
     *
     * @param string $name      The executable name (without the extension)
     *
     * @return string|null The executable path or default value
     */
    public function find($name)
    {
        $dirs = array(
            $this->root . DIRECTORY_SEPARATOR . 'bin',
            $this->root . DIRECTORY_SEPARATOR . 'app'
        );

        $suffixes = array('');
        if ('\\' === DIRECTORY_SEPARATOR) {
            $pathExt = getenv('PATHEXT');
            $suffixes = array_merge($suffixes, $pathExt ? explode(PATH_SEPARATOR, $pathExt) : $this->suffixes);
        }
        foreach ($dirs as $dir) {
            if (@is_file($file = $dir.DIRECTORY_SEPARATOR.$name.'.php')) {
                return $file;
            }
            foreach ($suffixes as $suffix) {
                if (@is_file($file = $dir.DIRECTORY_SEPARATOR.$name.$suffix) && ('\\' === DIRECTORY_SEPARATOR || is_executable($file))) {
                    return $file;
                }
            }
        }

        return null;
    }

}