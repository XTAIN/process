<?php

namespace XTAIN\Tests\Process;

use XTAIN\Process\Shell;

class ShellTest extends \PHPUnit\Framework\TestCase
{
    public function testWhich()
    {
        $this->assertEquals('/usr/bin/bash', Shell::which('bash'));
    }

    public function testArrayWhich()
    {
        $this->assertEquals('/usr/bin/bash', Shell::which(array('foooo1234', 'bash')));
        $this->assertEquals('/usr/bin/bash', Shell::which('foooo1234', 'bash'));
        $this->assertEquals('/usr/bin/sh', Shell::which(array('sh', 'bash')));
        $this->assertEquals('/usr/bin/sh', Shell::which('sh', 'bash'));
    }

    public function testEscape()
    {
        $this->assertEquals("'bash'", Shell::escape('bash'));
        $this->assertEquals("'bash' 'test123'", Shell::escape('bash', 'test123'));
    }
}