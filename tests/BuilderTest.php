<?php

namespace XTAIN\Tests\Process;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use XTAIN\Process\Builder;

class BuilderTest extends AbstractBuilderTest
{
    public function testBuilder()
    {
        $builder = new Builder('sleep 1');

        $this->assertBuilder($builder);
        $this->assertEquals(array('sleep 1'), $builder->getCommand());
    }

}