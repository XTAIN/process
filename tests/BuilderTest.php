<?php

namespace XTAIN\Tests\Process;

use Symfony\Component\Process\Process;
use XTAIN\Process\Builder;
use XTAIN\Process\DaemonProcess;

class BuilderTest extends AbstractBuilderTest
{
    public function testBuilder()
    {
        $builder = new Builder('sleep 1');

        $this->assertBuilder($builder);
        $this->assertEquals(array('sleep 1'), $builder->getCommand());
    }

    public function testBuilderDaemon()
    {
        $builder = new Builder('sleep 1');

        $this->assertBuilder($builder);
        $this->assertInstanceOf(DaemonProcess::class, $builder->getDaemon());
    }

}